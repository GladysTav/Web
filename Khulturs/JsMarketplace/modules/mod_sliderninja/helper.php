<?php
/**
 * @version     1.1.0
 * @package     Slider Ninja
 *
 * @copyright   Copyright (C) 2017. Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Asfaque Ali Ansari <info@bhartiy.com> - http://www.bhartiy.com
 */

class modSliderNinja_Helper
{

    public static function getContent( $params )
    {
        return 'No content';
    }

	public static function getData( $params)
	{

		$sliderNinja_Params = array();
		$sliderNinja_Params['loadjquery'] = $params->get( 'loadjquery' );
		$sliderNinja_Params['sliderclass'] = $params->get( 'sliderclass' );
		$sliderNinja_Params['navarrows'] = $params->get( 'navarrows' );
		$sliderNinja_Params['dotnav'] = $params->get( 'dotnav' );
		$sliderNinja_Params['autoplay'] = $params->get( 'autoplay' );
		$sliderNinja_Params['autoplayspeed'] = $params->get( 'autoplayspeed' );
		$sliderNinja_Params['enablecaption'] = $params->get( 'enablecaption' );

		$sliderNinja_Params['enableitem1'] = $params->get( 'enableitem1' );
		$sliderNinja_Params['slideimg1'] = $params->get( 'slideimg1' );
		$sliderNinja_Params['title1'] = $params->get( 'title1' );
		$sliderNinja_Params['description1'] = $params->get( 'description1' );
		$sliderNinja_Params['enablebtn1'] = $params->get( 'enablebtn1' );
		$sliderNinja_Params['btn1'] = $params->get( 'btn1' );
		$sliderNinja_Params['btnlink1'] = $params->get( 'btnlink1' );

		$sliderNinja_Params['enableitem2'] = $params->get( 'enableitem2' );
		$sliderNinja_Params['slideimg2'] = $params->get( 'slideimg2' );
		$sliderNinja_Params['title2'] = $params->get( 'title2' );
		$sliderNinja_Params['description2'] = $params->get( 'description2' );
		$sliderNinja_Params['enablebtn2'] = $params->get( 'enablebtn2' );
		$sliderNinja_Params['btn2'] = $params->get( 'btn2' );
		$sliderNinja_Params['btnlink2'] = $params->get( 'btnlink2' );

		$sliderNinja_Params['enableitem3'] = $params->get( 'enableitem3' );
		$sliderNinja_Params['slideimg3'] = $params->get( 'slideimg3' );
		$sliderNinja_Params['title3'] = $params->get( 'title3' );
		$sliderNinja_Params['description3'] = $params->get( 'description3' );
		$sliderNinja_Params['enablebtn3'] = $params->get( 'enablebtn3' );
		$sliderNinja_Params['btn3'] = $params->get( 'btn3' );
		$sliderNinja_Params['btnlink3'] = $params->get( 'btnlink3' );

		$sliderNinja_Params['enableitem4'] = $params->get( 'enableitem4' );
		$sliderNinja_Params['slideimg4'] = $params->get( 'slideimg4' );
		$sliderNinja_Params['title4'] = $params->get( 'title4' );
		$sliderNinja_Params['description4'] = $params->get( 'description4' );
		$sliderNinja_Params['enablebtn4'] = $params->get( 'enablebtn4' );
		$sliderNinja_Params['btn4'] = $params->get( 'btn4' );
		$sliderNinja_Params['btnlink4'] = $params->get( 'btnlink4' );

		$sliderNinja_Params['enableitem5'] = $params->get( 'enableitem5' );
		$sliderNinja_Params['slideimg5'] = $params->get( 'slideimg5' );
		$sliderNinja_Params['title5'] = $params->get( 'title5' );
		$sliderNinja_Params['description5'] = $params->get( 'description5' );
		$sliderNinja_Params['enablebtn5'] = $params->get( 'enablebtn5' );
		$sliderNinja_Params['btn5'] = $params->get( 'btn5' );
		$sliderNinja_Params['btnlink5'] = $params->get( 'btnlink5' );

		$sliderNinja_Params['enableitem6'] = $params->get( 'enableitem6' );
		$sliderNinja_Params['slideimg6'] = $params->get( 'slideimg6' );
		$sliderNinja_Params['title6'] = $params->get( 'title6' );
		$sliderNinja_Params['description6'] = $params->get( 'description6' );
		$sliderNinja_Params['enablebtn6'] = $params->get( 'enablebtn6' );
		$sliderNinja_Params['btn6'] = $params->get( 'btn6' );
		$sliderNinja_Params['btnlink6'] = $params->get( 'btnlink6' );


		return $sliderNinja_Params;
	}
}

?>
