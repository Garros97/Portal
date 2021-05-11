<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Tag[] $tags The current tags */
/** @var mixed $modelPrimaryKey The primary key of the base model, used for the delete links */
/** @var \TestApp\Model\Entity\Tag[] $usedTags Tags to show as autocomplete */
?>
<?php
$tags = collection($tags);
if ($usedTags === null) {
    $usedTags = [];
}
$mayEditTags = in_array('EDIT_TAGS', $this->request->getSession()->read('Auth.User.rights'));

echo '<div class="form-group">';
echo $this->Form->label('Tags');

echo '<div class="form-inline tags">';
foreach ($tags as $tag) {
    $tagName = h($tag->name);
    $tagValue = h($tag->_joinData->value);
    $html = "<span class=\"label label-primary\">$tagName<span class=\"badge\">$tagValue</span></span> ";
    if ($mayEditTags) {
        echo $this->Html->link($html,
            ['action' => 'deleteTag', $modelPrimaryKey, $tag->name],
            ['escape' => false, 'confirm' => sprintf("Tag %s wirklich löschen?", $tag->name)]);
    }
    else {
        echo $html;
    }

}
if ($mayEditTags) {
    echo $this->Form->control('newTag', [
        'label' => false,
        'placeholder' => 'Neuer Tag...',
        'title' => 'Format: name[:value]',
        'list' => 'usedTags',
        //'autocomplete' => 'off', //use this to tell the browser not to remember old (custom) entries
    ]);
}
?>
</div></div>
<datalist id="usedTags">
    <?php foreach ($usedTags as $tag): ?>
        <option value="<?= $tag ?>" >
    <?php endforeach; ?>
</datalist>