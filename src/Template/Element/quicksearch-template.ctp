<?php
//This are the templates for the entries shown by the quicksearch.
/** @var \App\View\AppView $this */
?>
<script type="text/html" id="qs-user-template">
    <a href="{url}" class="list-group-item">
        <span class="badge">UID{id}</span>
        <h5 class="list-group-item-heading">{first_name} {last_name}</h5>
        <p class="list-group-item-text">
            <?= $this->Html->icon('user') ?>&nbsp;{username} <?= $this->Html->icon('envelope') ?>&nbsp;{email}
        </p>
    </a>
</script>
<script type="text/html" id="qs-registration-template">
    <a href="{url}" class="list-group-item">
        <span class="badge">RID{id}</span>
        <!--<h5 class="list-group-item-heading">{first_name} {last_name}</h5>-->
        <p class="list-group-item-text">
            <?= $this->Html->icon('user') ?>&nbsp;{user:first_name} {user:last_name} ({user:username}) <?= $this->Html->icon('home') ?>&nbsp;{project:name}
        </p>
    </a>
</script>
<script type="text/html" id="qs-project-template">
    <a href="{url}" class="list-group-item">
        <span class="badge">PID{id}</span>
        <h5 class="list-group-item-heading">{name}</h5>
        <p class="list-group-item-text">{urlname}</p>
    </a>
</script>

<?php
//$debugMode = \Cake\Core\Configure::read('debug') ? 'true' : 'false';
$debugMode = 'false'; //enable this to disable caching for the quicksearch
$this->Html->scriptBlock(<<<JS
var qsApi = {
    projectPrefetchUrl: '{$this->Url->build(['controller' => 'quicksearch', 'action' => 'projectsPrefetch'])}',
    searchUsers: '{$this->Url->build(['controller' => 'quicksearch', 'action' => 'searchUsers', '?' => ['q' => 'QUERY']])}',
    searchRegistrations: '{$this->Url->build(['controller' => 'quicksearch', 'action' => 'searchRegistrations', '?' => ['q' => 'QUERY']])}',
    debugMode: {$debugMode}
}
JS
    , ['block' => 'libs']);
?>
