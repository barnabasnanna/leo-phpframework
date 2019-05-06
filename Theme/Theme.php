<?php
namespace Leo\Theme;
use Leo\ObjectBase;

/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 07/11/2018
 * Time: 23:55
 */

class Theme extends ObjectBase
{
    protected $name = '';
    protected $themeDates = array(
        'default' => '2015/11/05',
        'jasmine' => '2018/11/03 - 2018/12/31',
        'xmas' => '2018/12/25 - 2018/12/26'
    );

    public function setName($name){
        $this->name = strval($name);
        return $this;
    }

    /**
     * @code
     * $today = 2018/12/26
     * array(
     *   jasmine' => '2018/12/01 - 2018/12/31',// date falls within range so valid
     *   xmas' => '2018/12/25 - 2018/12/26' //date also falls within range so valid too
     *  )
     *
     * xmas is returned because, the start date is closest to today's date
     *
     * @endcode
     *
     * @return string the name of the theme with a start date closest to today's date
     */
    public function getName(){
        if($this->name==='' && is_array($this->themeDates) && count($this->themeDates)) {//if not yet set, calculate
            $startDates = [];
            $today = new \DateTime('now'); //today's date
            foreach ($this->themeDates as $name => $dateRange) {
                @list($start, $end) = explode('-', $dateRange);
                $startDate = \DateTime::createFromFormat('Y/m/d', trim($start));
                $endDate = \DateTime::createFromFormat('Y/m/d', trim($end));

                //Get the days difference between the start dates and today
                if ($today >= $startDate && (($end && $today <= $endDate) OR (!$end))) {
                    $startDates[$name] = ($startDate->diff($today))->format('%a');
                }
            }

            /**
             * Sort according to days difference. With least on top
             */
            uasort($startDates, function ($a, $b) {
                return (int)$a > (int)$b;
            });

            //return the theme name closest to today else default
            $this->name = key($startDates);
        }
        return $this->name;

    }



}