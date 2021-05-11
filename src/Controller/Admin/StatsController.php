<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Http\CallbackStream;
use Cake\I18n\Time;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * Stats Controller
 */
class StatsController extends AppController
{
    public $rights = [
        'index' => ['MANAGE_PROJECTS/?'],
        'getMap' => ['MANAGE_PROJECTS/?']
    ];

    protected function _buildProjectFilter($id, $assoc = '')
    {
        return function ($q) use ($id, $assoc) {
            if ($id !== null) {
                return $q->where([($assoc === '' ? 'project_id IN' : "$assoc.project_id IN") => $id]);
            } else {
                return $q;
            }
        };
    }

    public function getRequiredSubresourceIds($right, $request)
    {
        if (!isset($request->getParam('pass')[0])) {
            return 'any';
        }
        return $this->_prepareIds($request->getParam('pass')[0]); //you need the MANAGE_PROJECTS right for every project involved
    }


    /**
     * Index method
     *
     * @param string|null $id Project id(s).
     * @return \Cake\Http\Response
     */
    public function index($id = null)
    {
        $oldNoAutoLoad = Configure::read('noAutoLoad');
        Configure::write('noAutoLoad', true); //disable all automatic query modification

        //Note: This is a huge method, doing a lot of fancy stuff with the query builder.
        //One might separate this into single methods, but after all its just a lot of
        //quite complicated queries... When debugging this, always have a look at the
        //actual queries issued in DebugKit!

        //check for POST from project selector
        if ($this->request->is('post')) {
            $this->redirect(['action' => 'index', implode('-', $this->request->getData('projectSelector'))]);
        }

        //handle the url parameters
        $globalStats = $id === null; //this must be done before filtering based on rights!
        $this->set(compact('globalStats'));
        $id = $this->_prepareIds($id);
        $projects = TableRegistry::get('Projects')->find('list')->order('Projects.name');
        $this->set(compact('projects'));
        $this->set(['projectId' => $id]);

        #region general
        $usersQuery = TableRegistry::get('Users')->find();
        $userSexCounts = $usersQuery->find('list', [
                'keyField' => 'sex',
                'valueField' => 'count'
            ])
            ->innerJoinWith('Registrations', $this->_buildProjectFilter($id, 'Registrations'))
            ->select([
                'sex' => 'Users.sex',
                'count' => $usersQuery->func()->count('Users.id')
            ])
            ->group('sex')
            ->enableHydration(false)
            ->toArray() + ['m' => 0, 'f' => 0];
        $teacherCount = TableRegistry::get('Users')->find()
            ->innerJoinWith('Registrations', $this->_buildProjectFilter($id, 'Registrations'))
            ->innerJoinWith('Tags', function ($q) {
                return $q->where(['Tags.name' => 'isTeacher']);
            })->count();
        $groupsQuery = TableRegistry::get('Groups')->find();
        $projectFilter = $this->_buildProjectFilter($id);
        // create clean copy for group count, otherwise the following inner join in the avgGroupSize query
        // would also be applied to the groupCount query so we would only count non-empty groups
        $groupCount = $projectFilter($groupsQuery->cleanCopy())->count();
        $avgGroupSize = $groupsQuery->cleanCopy()->select(['avg' => $groupsQuery->func()->avg('cnt')])
            ->from(['subquery' => $projectFilter($groupsQuery->select(['cnt' => $groupsQuery->func()->count('Groups.id')])
                ->innerJoinWith('Users')
                ->group('Groups.id'))])
            ->enableHydration(false)
            ->firstOrFail()['avg'];

        $this->set(compact('userSexCounts', 'teacherCount', 'groupCount', 'avgGroupSize'));
        #endregion

        #region history
        $historyCutoffDate = new Time('90 days ago');
        $historyQuery = TableRegistry::get('Registrations')->find();
        $projectFilter($historyQuery);
        $historyData = $historyQuery->find('list', [
                'keyField' => 'date',
                'valueField' => 'cnt'
            ])
            ->select([
                'date' => $historyQuery->func()->greatest([$historyCutoffDate, $historyQuery->func()->date(['created' => 'literal'])], ['date', 'date']),
                'cnt' => $historyQuery->func()->count('id')
            ])
            ->group('date')
            ->enableHydration(false)
            ->toArray();

        $this->set(compact('historyData'));
        #endregion

        if (!$globalStats) { //some stats make only sense when a specific project is shown
            #region courses
            $coursesQuery = TableRegistry::get('Courses')->find();
            $coursesData = $coursesQuery->where(['Courses.project_id IN' => $id])
                ->innerJoinWith('Registrations.Users')
                ->select([
                    'id', 'name', 'max_users', 'waiting_list_length',
                    'male_users_cnt' => $coursesQuery->func()->coalesce([$coursesQuery->func()->sum($coursesQuery->newExpr()
                        ->addCase(
                            $coursesQuery->newExpr()->add(['Users.sex' => 'm']),
                            1,
                            'integer'
                        )), 0], ['integer', 'integer']),
                    'female_users_cnt' => $coursesQuery->func()->coalesce([$coursesQuery->func()->sum($coursesQuery->newExpr()
                        ->addCase(
                            $coursesQuery->newExpr()->add(['Users.sex' => 'f']),
                            1,
                            'integer'
                        )), 0], ['integer', 'integer']),
                    'users_cnt' => $coursesQuery->func()->count('Users.id')
                ], true)
                ->group(['Courses.id'])
                ->enableHydration(false)
                ->toArray();

            $this->set(compact('coursesData'));
            #endregion

            #region uploads
            $coursesQuery = TableRegistry::get('Courses')->find();
            $uploadsData = $coursesQuery->find('list', [
                    'keyField' => 'name',
                    'valueField' => 'upload_cnt'
                ])
                ->where(['Courses.project_id IN' => $id, 'Groups.project_id IN' => $id]) // we also have to require that the groups belong to the wanted project(s)!
                ->select([
                    'name' => 'Courses.name',
                    'upload_cnt' => $coursesQuery->func()->count('DISTINCT Groups.id')
                ])
                ->innerJoinWith('UploadedFiles.Users')
                ->innerJoinWith('UploadedFiles.Users.Groups', function ($q) {
                    return $q->where(['UploadedFiles.is_deleted' => false]);
                })
                ->group(['Courses.id'])
                ->enableHydration(false)
                ->toArray();

            $this->set(compact('uploadsData'));
            #endregion

            #region custom fields
            $customFieldsQuery = TableRegistry::get('CustomFields')->find();
            $customFieldsData = $customFieldsQuery
                ->innerJoinWith('Registrations')
                ->where(['Registrations.project_id IN' => $id])
                ->select([
                    'CustomFields.name',
                    'CustomFields.type',
                    'value' => 'CustomFieldsRegistrations.value',
                    'cnt' => $customFieldsQuery->func()->count('1')
                ])
                ->group(['CustomFields.id', 'CustomFieldsRegistrations.value'])
                ->order(['CustomFields.section', 'CustomFields.name' ,'cnt' => 'DESC'])
                ->enableHydration(false)
                ->groupBy('name');

            $this->set(compact('customFieldsData'));
            #endregion
        }

        Configure::write('noAutoLoad', $oldNoAutoLoad); //restore all automatic query modification
    }

    public function getMap($id = null)
    {
        $id = $this->_prepareIds($id);

        $region = $this->request->getQuery('region');
        $region = $region == null ? 'germany' : $region;
        $dotSize = $this->request->getQuery('dotSize');
        $dotSize = $dotSize == null ? 42 : $dotSize;

        //Code taken from the old portal's map.php, that's why it's a bit... strange

        //allocate image and colors
        $image = imagecreatefrompng(WWW_ROOT . 'img' . DS . 'Deutschland.png');

        $amountColors = [
            imagecolorallocate($image, 0, 255, 0),
            imagecolorallocate($image, 20, 235, 0),
            imagecolorallocate($image, 40, 215, 0),
            imagecolorallocate($image, 60, 195, 0),
            imagecolorallocate($image, 80, 175, 0),
            imagecolorallocate($image, 100, 155, 0),
            imagecolorallocate($image, 120, 135, 0),
            imagecolorallocate($image, 140, 115, 0),
            imagecolorallocate($image, 160, 95, 0),
            imagecolorallocate($image, 180, 75, 0),
            imagecolorallocate($image, 200, 55, 0),
            imagecolorallocate($image, 220, 35, 0),
            imagecolorallocate($image, 240, 15, 0),
            imagecolorallocate($image, 255, 0, 0)
        ];
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $borderColor = imagecolorallocate($image, 0, 0, 0);

        //draw cities
        $cities = [
            [52.374444, 9.738611, "Hannover"],
            [52.518333, 13.408333, "Berlin"],
            [53.075878, 8.807311, "Bremen"],
            [51.049259, 13.73836, "Dresden"],
            [51.225556, 6.782778, "Düsseldorf"],
            [50.978056, 11.029167, "Erfurt"],
            [53.550556, 9.993333, "Hamburg"],
            [54.325278, 10.140556, "Kiel"],
            [52.133333, 11.616667, "Magdeburg"],
            [50.000000, 8.2711110, "Mainz"],
            [48.133333, 11.566667, "München"],
            [52.395833, 13.061389, "Potsdam"],
            [49.233333, 7.0000000, "Saarbrücken"],
            [53.633333, 11.416667, "Schwerin"],
            [48.776111, 9.1775, "Stuttgart"],
            [50.083333, 8.25, "Wiesbaden"],
            [50.942222, 6.957778, "Köln"],
            [51.962944, 7.628694, "Münster"],
            [50.113611, 8.679722, "Frankfurt"],
            [51.458069, 7.014761, "Essen"],
            [51.340333, 12.37475, "Leipzig"],
            [49.452778, 11.077778, "Nürnberg"],
            [52.016667, 8.516667, "Bielefeld"],
            [48.371667, 10.898333, "Augsburg"],
            [50.776667, 6.083611, "Aachen"],
            [52.269167, 10.521111, "Braunschweig"],
            [54.083333, 12.133333, "Rostock"],
            [51.716667, 8.766667, "Paderborn"],
            [51.533889, 9.935556, "Göttingen"],
            //[52.15,10.333333,"Salzgitter"], //I have no idea why this is excluded...
            [52.15, 9.95, "Hildesheim"]
        ];

        //get user data from DB
        $userQuery = TableRegistry::get('Users')->find();
        $projectFilter = $this->_buildProjectFilter($id, 'Registrations');
        $projectFilter($userQuery);
        $geonamesDbName = Configure::read('Misc.geonamesDbName');
        $userCoords = $userQuery->select(['pc.lat', 'pc.lon', 'cnt' => $userQuery->func()->count('1')])
            ->applyOptions(['noAutoContainTags' => true])
            ->innerJoinWith('Registrations')
            ->join(['pc' => ['table' => "$geonamesDbName.postal_codes", 'conditions' => 'Users.postal_code = pc.plz']])
            ->group(['pc.lat', 'pc.lon'])
            ->order('cnt')
            ->enableHydration(false)
            ->map(function ($elm) {
                return [
                    'cnt' => $elm['cnt'],
                    'lat' => $elm['pc']['lat'],
                    'lon' => $elm['pc']['lon']
                ];
            })
            ->toArray();

        //we draw in two loops to achieve the cool effect with the black border
        foreach ($userCoords as $coord) {
            list($x, $y) = $this->_mapToLocalCoords([$coord['lat'], $coord['lon']]);
            imagefilledellipse($image, $x, $y, $dotSize, $dotSize, $borderColor);
        }

        foreach ($userCoords as $coord) {
            $sz = round(sqrt($coord['cnt'])) * 5;
            list($x, $y) = $this->_mapToLocalCoords([$coord['lat'], $coord['lon']]);
            imagefilledellipse($image, $x, $y, $dotSize - 6, $dotSize - 6, $this->_mapAmountColor($amountColors, $sz));
        }

        //draw cities as overlay
        foreach ($cities as $c) {
            list($x, $y) = $this->_mapToLocalCoords($c);
            imagefilledellipse($image, $x, $y, 10, 10, $textColor);
            imagettftext($image, 10, 0, $x + 10, $y, $textColor, '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf', $c[2]);
        }

        //clip the image to the requested region
        switch($region) {
            case 'germany':
                $clip = [0,0,1407,1921];
                break;
            case 'lower-saxony':
                $clip = [115,300,760,650];
                break;
            case 'nrw':
                $clip = [0,630,570,550];
                break;
            default:
                throw new \LogicException("region $region is unknown");
        }

        $imageClipped=imagecreatetruecolor($clip[2],$clip[3]);
        imagecopy($imageClipped,  $image, 0, 0, $clip[0], $clip[1], $clip[2], $clip[3]);

        $this->response = $this->response
            ->withType('png')
            ->withBody(new CallbackStream(function () use ($imageClipped) {
                imagepng($imageClipped);
            }));
        if (!Configure::read('debug')) {
            $this->response = $this->response->withCache('-1 minute', '+10 min');
        }

        return $this->response;
    }

    protected function _mapAmountColor($colors, $amount)
    {
        $max = 50;

        $i = round(count($colors) / $max * $amount) - 1;
        if ($i > count($colors) - 1) {
            $i = count($colors) - 1;
        }
        if ($i < 0) {
            $i = 0;
        }

        return $colors[$i];
    }

    protected function _mapToLocalCoords($coords)
    {
        //magic code taken from old portal
        $y = 1407 - round(1407 * ($coords[0] - 49.3) / (55.21 - 49.3));
        $x = round(1921 * ($coords[1] - 5.81) / (18.52 - 5.81));
        return [$x, $y];
    }

    protected function _prepareIds($id)
    {
        if ($id !== null) {
            $id = explode('-', $id); //multiple projects supported as "1-9-13"
            if (!collection($id)->every(function ($x) { return ctype_digit((string)$x); })) {
                throw new NotFoundException(); //note that this will not trigger for not existing projects
            }
        } else { //$id is null, global stats requested
            $accessibleSubresources = $this->Auth->userGetAccessibleSubresourceIds('MANAGE_PROJECTS');
            if ($accessibleSubresources !== true) { //no global access
                return $accessibleSubresources;
            }
        }

        return $id;
    }
}
