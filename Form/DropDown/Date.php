<?php
namespace Leo\Form\DropDown;

use Leo\ObjectBase;
/**
 * Description of Date
 *
 * @author barnabasnanna
 */
class Date extends ObjectBase
{

    protected $date_value,
            $month_value,
            $year_value,
            $start_year,
            $name,
            $day,
            $month,
            $year,
            $options,
            $label='',
            $label_options =[],
            $template = '<%s %s><label %s for="%s">%s</label>%s%s%s</%s>',
            $visible=true,
            $wrap='div',
            $wrap_options=['style'=>'margin:0 0 20px 0'],
            $end_year;
    
    
    
       
    public function __construct($name, array $options=[])
    {
        parent::__construct();
        $this->setName($name);
        $this->setOptions($options);
    }
    
    public function _start_()
    {
        parent::_start_();

        $this->start_year = $this->start_year ? : \date('Y') - 5;
        $this->end_year = $this->end_year ? : \date('Y');
        $this->month = (int) date('m');
        $this->day = date('j');
        $this->year =  date('Y');
    }

    public function getDays()
    {
        $days = [];
        for($i=1;$i<32;$i++)
        {
            $days[$i] = $i;
        }
        
        return $days;
    }

    public  function getMonths()
    {
        return array(
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        );
    }
    
    public function getYears()
    {
        $years = [];
        for($i=$this->start_year;$i<=$this->end_year;$i++)
        {
            $years[$i] = $i;
        }
        
        return $years;
    }
    
    public function __toString()
    {//            $template = '<%s %s><label %s for="%s">%s</label><br/>%s%s%s</%s>',

        return sprintf($this->getTemplate(),
                $this->getWrap(),
                $this->getWrapOptions(),
                $this->getLabelOptions(),
                $this->getName(),
                $this->getLabel(),
                $this->getDayInput(),
                $this->getMonthInput(),
                $this->getYearInput(),
                $this->getWrap());
    }
    
    public function getDayInput()
    {
        $template ='<label %s for="%s">%s</label><select id="%s" name="%s" %s>%s</select>';
        $name = $this->getName().'_day';
        $day = new Select($name);
        return $day->setLabelOptions(['class'=>'sr-only'])
            ->setDropDownOptions(self::getDays())->setTemplate($template)
            ->setValue($this->day);
    }
    
    public function getMonthInput()
    {
        $template ='<label %s for="%s">%s</label><select id="%s" name="%s" %s>%s</select>';
        $name  = $this->getName().'_month';
        $month = new Select($name);
        return $month->setLabelOptions(['class'=>'sr-only'])
              ->setDropDownOptions(self::getMonths())->setTemplate($template)
              ->setValue($this->month);
    }
    
    public function getYearInput()
    {
        $template ='<label %s for="%s">%s</label><select id="%s" name="%s" %s>%s</select>';
        $name = $this->getName().'_year';
        $year = new Select($name);
        return $year->setLabelOptions(['class'=>'sr-only'])
                ->setDropDownOptions(self::getYears())->setTemplate($template)
                ->setValue($this->year);
    }
    
    
    public function getDate_value()
    {
        return $this->date_value;
    }

    public function getMonth_value()
    {
        return $this->month_value;
    }

    public function getYear_value()
    {
        return $this->year_value;
    }

    public function setDate_value($date_value)
    {
        $this->date_value = $date_value;
        return $this;
    }

    public function setMonth_value($month_value)
    {
        $this->month_value = $month_value;
        return $this;
    }

    public function setYear_value($year_value)
    {
        $this->year_value = $year_value;
        return $this;
    }
    
    public function getStart_year()
    {
        return $this->start_year;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMonth()
    {
        return $this->month;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function getEnd_year()
    {
        return $this->end_year;
    }

    public function setStart_year($start_year)
    {
        $this->start_year = $start_year;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    public function setEnd_year($end_year)
    {
        $this->end_year = $end_year;
        return $this;
    }
    
    public function getDay()
    {
        return $this->day;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setDay($day)
    {
        $this->day = $day;
        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
    
    /**
     * @param bool $visible
     */
    public function setVisible($visible)
    {
        $this->visible = boolval($visible);
    }
    
    /**
     * 
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
    }
        
    protected function getCleanName()
    {
        return ucwords($this->clean($this->getName(), true));
    }

    public function getLabel()
    {
        if (empty($this->label))
        {
            $this->setLabel($this->getCleanName());
        }

        return $this->label;
    }

    public function getLabelOptions()
    {
        $label_options = '';

        foreach ($this->label_options as $attr => $value)
        {
            $label_options.= " $attr = '$value' ";
        }

        return $label_options;
    }
    
    public function getWrapOptions()
    {
        $wrap_options = '';

        foreach ($this->wrap_options as $attr => $value)
        {
            $wrap_options.= " $attr = '$value' ";
        }

        return $wrap_options;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function setLabelOptions($label_options)
    {
        $this->label_options = $label_options;
        return $this;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    
     /**
     * replaces all non alpha numeric characters in string to space
     * @param string $str string you want cleaned
     * @param boolean $lowerCase set to true if you want a lowercased version returned
     * @return string sanitized string
     */
    protected function clean($str = '', $lowerCase = false)
    {
        $s = preg_replace('/[^A-Za-z0-9]/', ' ', $str);
        return $lowerCase ? strtolower($s) : $s;
    }

    public function getWrap()
    {
        return $this->wrap;
    }

    public function setWrap($wrap)
    {
        $this->wrap = $wrap;
        return $this;
    }

    public function setWrapOptions(array $wrap_options)
    {
        $this->wrap_options = $wrap_options;
        return $this;
    }



}
