<%
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Utility\Inflector;

$fields = collection($fields)
    ->filter(function($field) use ($schema) {
        return !in_array($schema->columnType($field), ['binary', 'text']);
    });

if (isset($modelObject) && $modelObject->behaviors()->has('Tree')) {
    $fields = $fields->reject(function ($field) {
        return $field === 'lft' || $field === 'rght';
    });
}
%>
<nav class="actions col-sm-4 col-xs-12">
    <ul class="side-nav nav nav-pills nav-stacked">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New <%= $singularHumanName %>'), ['action' => 'add']) ?></li>
<%
    $done = [];
    foreach ($associations as $type => $data):
        foreach ($data as $alias => $details):
            if (!empty($details['navLink']) && $details['controller'] !== $this->name && !in_array($details['controller'], $done)):
%>
        <li><?= $this->Html->link(__('List <%= $this->_pluralHumanName($alias) %>'), ['controller' => '<%= $details['controller'] %>', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New <%= $this->_singularHumanName($alias) %>'), ['controller' => '<%= $details['controller'] %>', 'action' => 'add']) ?></li>
<%
                $done[] = $details['controller'];
            endif;
        endforeach;
    endforeach;

    $namespace = Configure::read('App.namespace');

    $skipFields = ['password', 'slug', 'lft', 'rght', 'created_by', 'modified_by', 'approved_by', 'deleted_by'];
    if (property_exists($modelObject, 'scaffoldSkipFieldsIndex')) {
        $skipFields = array_merge($skipFields, (array)$modelObject->scaffoldSkipFieldsIndex);
    }
    if (property_exists($modelObject, 'scaffoldSkipFields')) {
        $skipFields = array_merge($skipFields, (array)$modelObject->scaffoldSkipFields);
    }

    // We want certain fields to be paginated DESC by default, also certain types
    $paginationOrderReversedFields = ['published', 'rating', 'priority'];
    if (property_exists($modelObject, 'paginationOrderReversedFields')) {
        $paginationOrderReversedFields = array_merge($paginationOrderReversedFields, (array)$modelObject->paginationOrderReversedFields);
    }
    $paginationOrderReversedFieldTypes = ['datetime', 'date', 'time', 'bool'];
    if (property_exists($modelObject, 'paginationOrderReversedFieldTypes')) {
        $paginationOrderReversedFieldTypes = array_merge($paginationOrderReversedFieldTypes, (array)$modelObject->paginationOrderReversedFieldTypes);
    }
%>
    </ul>
</nav>
<div class="<%= $pluralVar %> index col-sm-8 col-xs-12">
    <h3><?= __('<%= $pluralHumanName %>') ?></h3>
    <table class="table">
        <thead>
            <tr>
<% foreach ($fields as $field): %>
<%
    $primaryKeys = $schema->primaryKey();
    if (in_array($field, $primaryKeys)) {
        continue;
    }

    if (in_array($field, $skipFields) || (false && $field === 'sort' && $upDown)) {
        continue;
    }

    $options = '';
    $fieldData = $schema->column($field);
    if (in_array($field, $paginationOrderReversedFields) || in_array($fieldData['type'], $paginationOrderReversedFieldTypes)) {
        $options = ", null, ['direction' => 'desc']";
    }
%>
                <th><?= $this->Paginator->sort('<%= $field %>'<%= $options %>) ?></th>
<% endforeach; %>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($<%= $pluralVar %> as $<%= $singularVar %>): ?>
            <tr>
<%
    foreach ($fields as $field) {
        $fieldType = $schema->columnType($field);
        $isKey = false;

        $primaryKeys = $schema->primaryKey();
        if (in_array($field, $primaryKeys)) {
            continue;
        }

        if (in_array($field, $skipFields) || (false && $field === 'sort' && $upDown)) {
            continue;
        }

        if (!empty($associations['BelongsTo'])) {
            foreach ($associations['BelongsTo'] as $alias => $details) {
                if ($field === $details['foreignKey']) {
                    $isKey = true;
%>
                <td><?= $<%= $singularVar %>->has('<%= $details['property'] %>') ? $this->Html->link($<%= $singularVar %>-><%= $details['property'] %>-><%= $details['displayField'] %>, ['controller' => '<%= $details['controller'] %>', 'action' => 'view', $<%= $singularVar %>-><%= $details['property'] %>-><%= $details['primaryKey'][0] %>]) : '' ?></td>
<%
                    break;
                }
            }
        }
        if ($isKey !== true) {
%>
<% if (in_array($fieldType, ['integer']) && method_exists($namespace . '\Model\Entity\\' . ($entityClass = ucfirst($singularVar)), $enumMethod = lcfirst(Inflector::camelize(Inflector::pluralize($field))))): %>
                <td><?= $<%= $singularVar %>::<%= $enumMethod%>($<%= $singularVar %>-><%= $field %>) ?></td>
<% elseif (in_array($fieldType, ['integer', 'float', 'decimal', 'biginteger'])): %>
                <td><?= $this->Number->format($<%= $singularVar %>-><%= $field %>) ?></td>
<% elseif (in_array($fieldType, ['date', 'time', 'datetime', 'timestamp'])): %>
                <td><?= $this->Time->nice($<%= $singularVar %>-><%= $field %>) ?></td>
<% elseif (in_array($fieldType, ['boolean'])): %>
                <td><?= $this->Format->yesNo($<%= $singularVar %>-><%= $field %>) ?></td>
<% else: %>
                <td><?= h($<%= $singularVar %>-><%= $field %>) ?></td>
<% endif; %>
<%
        }
    }
    $pk = '$' . $singularVar . '->' . $primaryKey[0];
%>
                <td class="actions">
<%
    // Sortable Behavior buttons
    if (!empty($upDown)) {
%>
                <?= $this->Html->link($this->Format->icon('up'), ['action' => 'up', <%= $pk %>], ['escape' => false]); ?>
                <?= $this->Html->link($this->Format->icon('down'), ['action' => 'down', <%= $pk %>], ['escape' => false]); ?>
<%
    }
%>
                <?= $this->Html->link($this->Format->icon('view'), ['action' => 'view', <%= $pk %>], ['escape' => false]); ?>
                <?= $this->Html->link($this->Format->icon('edit'), ['action' => 'edit', <%= $pk %>], ['escape' => false]); ?>
                <?= $this->Form->postLink($this->Format->icon('delete'), ['action' => 'delete', <%= $pk %>], ['escape' => false, 'confirm' => __('Are you sure you want to delete # {0}?', <%= $pk %>)]); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php echo $this->element('Tools.pagination'); ?>
</div>
