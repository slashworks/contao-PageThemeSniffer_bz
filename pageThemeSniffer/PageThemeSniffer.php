<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Stefan Melz 2012
 * @author     Stefan Melz <www.borowiakziehe.de>
 * @author     Joe Ray Gregory <www.borowiakziehe.de>
 * @package    sm_pageLayout
 * @license    LGPL
 * @filesource
 */

class PageThemeSniffer extends Backend
{
    /**
     * Returns a data object
     * @param $data
     * @return array
     */
    private function _generateDataArr($data) {
        $itemArr = array(
            'layout' => array(
                'id' => $data[0],
                'name' => $data[1]
            ),
            'theme' => array(
                'id' => $data[2],
                'name' => $data[3]
            )
        );

        return json_decode(json_encode ($itemArr), FALSE);
    }

    /**
     * check if there are parent layouts
     * @param $pid
     * @return mixed
     */
    private function checkParentLayout($pid)
    {
        $checkParentLayout = $this->Database->prepare
        ('
          SELECT
            p.pid AS pid,
            p.layout,
            p.includeLayout,
            l.name AS name,
            t.name AS themename,
            t.id AS themeId
           FROM
            tl_page p
              LEFT JOIN tl_layout l ON (p.layout=l.id)
              LEFT JOIN tl_theme t ON (l.pid=t.id)
           WHERE
            p.id = ?
        ')->execute($pid);

        //if Parent has Layout
        if($checkParentLayout->layout > 0 && $checkParentLayout->includeLayout == 1)
        {
            $output = $this->_generateDataArr(array($checkParentLayout->themeId, $checkParentLayout->themename, $checkParentLayout->layout, $checkParentLayout->name));
        }
        //else check Parent from Parent
        else
        {
             //if page has parent
            if($checkParentLayout->pid > 0)
            {
                $output = $this->checkParentLayout($checkParentLayout->pid);
            }
            //if Page has no Parent
            else
            {
                $checkDefaultLayout = $this->Database->prepare('
                    SELECT
                      l.id,
                      l.name,
                      t.name AS themename,
                      t.id AS themeId
                    FROM
                      tl_layout l
                        LEFT JOIN tl_theme t ON (l.pid=t.id)
                      WHERE
                        fallback = ?
                ')->execute(1);

                //if fallback Layout exist
                if($checkDefaultLayout->numRows > 0)
                {
                    $output = $this->_generateDataArr(array($checkDefaultLayout->themeId, $checkDefaultLayout->themename, $checkDefaultLayout->id, $checkDefaultLayout->name));
                }

                //no Layout and no Fallbacklayout

                else
                {
                    $output = array
                    (
                        $GLOBALS['TL_LANG']['PageThemeSniffer']['noLayout']
                    );
                }
            }
        }
        return $output;
    }

    /**
     * @param $varValue
     * @return array|mixed
     */
    public function findThemeData($varValue)
    {
        $checklayout = $this->Database->prepare('
          SELECT
              p.id,p.pid AS ppid,p.title,p.layout,p.includeLayout,
              l.pid,l.name AS name,
              t.name as themename,
              t.id as themeId
            FROM
              tl_page p
                LEFT JOIN
                  tl_layout l ON (p.layout=l.id)
                LEFT JOIN
                  tl_theme t ON (l.pid=t.id)
            WHERE p.id = ?
        ')->execute($varValue->id);

        //if layout is directly selected

        if($checklayout->layout > 0 && $checklayout->includeLayout == 1)
        {
            $output = $this->_generateDataArr(array($checklayout->themeId, $checklayout->themename, $checklayout->layout, $checklayout->name));
        }

        //else check Parent
        else
        {
            $output = $this->checkParentLayout($checklayout->ppid);
        }

        return $output;
    }
}
