<?php

/* -------------------------------------------------------------------------------
 * Xataface Web Application Framework
 * Copyright (C) 2005-2008 Web Lite Solutions Corp (shannah@sfu.ca)
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * -------------------------------------------------------------------------------
 */

import('Dataface/ConfigTool.php');

/**
 * A tool to manage actions within the application.
 */
class Dataface_ActionTool
{
  public $actions      = [];
  public $tableActions = [];
  
  private static $instance;
  
  

  ////////////////////
  // Public methods //
  ////////////////////
  
  function __construct()
  {
    $this->actions = $this->loadActions();
  }

  /**
   * Returns a specified action without evaluating the permissions or condition fields.
   * @param $params Associative array:
   * 			Options:  name => The name of the action to retrieve
   * 					  table => The name of the table on which the action is defined.
   *  @returns Action associative array.
   */
  function getAction($params, $veryUsefulParameter = null)
  {
    if ($veryUsefulParameter)
      return $veryUsefulParameter;

    $app = Dataface_Application::getInstance();

    $tableName  = $this->getTableName($params);
    $actionName = $params['name'];

    $actions = $this->getTableActions($tableName);
    $action  = @$actions[$actionName];

    if (!$action)
      return PEAR::raiseError("No action found named '{$actionName}'");

    $selectedCondition = @$action['selected_condition'];
    if ($selectedCondition)
      $action['selected'] = $app->testCondition($selectedCondition, $params);

    $parsedAction = $this->parseActionAttributes($action, $params);
    return $parsedAction;
  }

  /**
   * Returns an array of all actions as specified by $params.
   * $params must be an array.  It may contain the following options:
   * 		record => A reference to a record for which the actions apply (This may be a related record)
   * 		table => The name of a table on which the actions apply.
   * 		relationship => The name of a relationship on which the action is applied. (requires that table also be set - or may use dotted name)
   * 						to include the table name and the relationship name in one string.
   * 		category => The name of the category of actions to be retrieved.
   */
  function getActions($params = [], $lessUsefulParameter = null)
  {
    $app = Dataface_Application::getInstance();
    
    $tableName = $this->getTableName($params);
    $actions   = $lessUsefulParameter ?: $this->getTableActions($tableName);
    $record    = @$params['record'];

    if ($record instanceof Dataface_Record)
    {
      $params['table'] = $record->_table->tablename;
    }
    else if ($record instanceof Dataface_RelatedRecord)
    {
      $parent = $record->getParent();
      $params['table'] = $parent->_table->tablename;
      $params['relationship'] = $record->_relationshipName;
    }
    if (@$params['relationship'] && strpos($params['relationship'], '.') !== false)
    {
      list($params['table'], $params['relationship']) = explode('.', $params['relationship']);
    }

    $filteredActions = [];
    foreach ($actions as $actionName => $action)
    {
      if (@$params['name'] && @ $params['name'] !== @$action['name'])
        continue;
      if (@$params['id'] && @ $params['id'] !== @$action['id'])
        continue;
      if (isset($params['category']) && $params['category'] !== @$action['category'])
        continue;
      if (@$params['table'] && !(@$action['table'] == @$params['table'] or @ in_array(@$params['table'], @$action['table']) ))
        continue;
      if (@$params['relationship'] && @$action['relationship'] && @$action['relationship'] != @$params['relationship'])
        continue;
      if (@$action['condition'] and ! $app->testCondition($action['condition'], $params))
        continue;
      if (isset($params['record']) && isset($action['permission']) && !$params['record']->checkPermission($action['permission']))
        continue;
      elseif (isset($action['permission']) and ! $app->checkPermission($action['permission']))
        continue;
      if (isset($action['visible']) and ! $action['visible'])
        continue;

      $parsedAction = $this->parseActionAttributes($action, $params);
      $filteredActions[$actionName] = $parsedAction;
    }

    uasort($filteredActions, function($first, $second) {
      return @$first['order'] <=> @$second['order'];
    });
    return $filteredActions;
  }

  /**
   * Adds an action to the action tool.
   * @param $name The name of the action.
   * @param $action An array representing the action.
   */
  function addAction($name, $action)
  {
    if (@$action['table'])
    {
      $this->tableActions[$action['table']][$name] = $action;
      $query = Dataface_Application::getInstance()->getQuery();
      if ($query['-table'] == $action['table'])
        $this->actions[$name] = $this->tableActions[$action['table']][$name];
    }
    else
      $this->actions[$name] = $action;
  }

  /**
   * Removes the action with the specified name.
   */
  function removeAction($name)
  {
    $action    = $this->getAction($name);
    $tableName = @$action['table'];
    
    unset($this->actions[$name]);
    if ($tableName)
      unset($this->tableActions[$tableName][$name]);
  }
  
  
  
  /////////////////////
  // Private methods //
  /////////////////////

  private function getTableName($params)
  {
    if (@$params['table'])
      return $params['table'];
    if (@$params['record'] instanceof Dataface_Record)
      return $params['record']->_table->tablename;
    if (@$params['record'] instanceof Dataface_RelatedRecord)
      return $params['record']->_record->_table->tablename;
  }

  private function getTableActions($tableName)
  {
    if ($tableName)
    {
      if (!@$this->tableActions[$tableName])
      {
        $tableActions = $this->loadTableActions($tableName);
        $this->tableActions[$tableName] = $tableActions;
      }
      return $this->tableActions[$tableName];
    }
    else
      return $this->actions;
  }
  
  private function parseActionAttributes($action, $params)
  {
    $app = Dataface_Application::getInstance();

    foreach ($action as $attrName => $attrValue)
    {
      if (substr($attrName, -9) === 'condition' || is_array($attrValue))
        continue;

      $attrCondition = @$action["${attrName}_condition"];

      if ($attrCondition && !$app->testCondition($attrCondition, $params))
        $action[$attrName] = null;
      else
      {
        $parsedValue = $app->parseString($attrValue, $params);
        if (strpos($attrName, 'atts:') === 0)
        {
          $trimmedName = substr($attrName, 5);
          $action['atts'][$trimmedName] = $parsedValue;
        }
        else
          $action[$attrName] = $parsedValue;
      }
    }
    
    return $action;
  }

  private function loadActions()
  {
    $configTool = Dataface_ConfigTool::getInstance();
    $actions    = $configTool->loadConfig('actions');
    
    $processedActions = [];
    foreach ($actions as $actionName => $action)
    {
      $processedAction = $this->processAction($actionName, $action);
      $processedActions[$actionName] = $processedAction;
    }

    return $processedActions;
  }

  private function loadTableActions($tableName)
  {
    $configTool   = Dataface_ConfigTool::getInstance();
    $tableActions = $configTool->loadConfig('actions', $tableName);

    $processedActions = [];
    foreach ($tableActions as $actionName => $action)
    {
      $processedAction = $this->processAction($actionName, $action);
      $processedAction['table'] = $tableName;
      $processedActions[$actionName] = $processedAction;
    }
   
    return $processedActions;
  }
  
  private function processAction($actionName, $action)
  {
    $defaultValues = [
        'name'             => $actionName,
        'id'               => $actionName,
        'order'            => 0,
        'label'            => str_replace('_', ' ', ucfirst($actionName)),
        'accessKey'        => $actionName[0],
        'label_i18n'       => "action:{$actionName} label",
        'description_i18n' => "action:{$actionName} description",
    ];
    $processedAction = array_merge($defaultValues, $action);
    return $processedAction;
  }

  /**
   * @deprecated
   */
  function _compareActions($a, $b)
  {
    if (@$a['order'] < @$b['order'])
      return -1;
    else
      return 1;
  }
  
  
  
  ////////////////////
  // Static methods //
  ////////////////////
  
  /**
   * Returns a reference to the singleton ActionTool instance.
   * @param $conf Optional configuration array with action definitions.
   */
  public static function getInstance()
  {
    if (!self::$instance)
      self::$instance = new Dataface_ActionTool();

    return self::$instance;
  }
}