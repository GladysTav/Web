<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Sellacious\Communication\CommunicationHelper;
use Sellacious\Config\ConfigHelper;
use Sellacious\Utilities\Otp;

/**
 * OtpAuth controller class
 *
 * @since   2.0.0
 */
class SellaciousControllerOtpAuth extends SellaciousControllerBase
{
	/**
	 * @var    string  The prefix to use with controller messages
	 *
	 * @since   2.0.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_OTPAUTH';

	/**
	 * Generate and send an OTP to the selected email/phone number
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function requestFieldOtp()
	{
		try
		{
			$field  = $this->input->getString('field');
			$type   = $this->input->getString('type');
			$value  = $this->input->getString('value');
			$unique = $this->input->getString('unique');
			$userid = $this->input->getString('userid');

			if ($unique && !$this->isUnique($userid, $type, $value))
			{
				throw new Exception(JText::_($this->text_prefix . '_IN_USE_' . strtoupper($type)));
			}

			$config   = ConfigHelper::getInstance('com_sellacious');

			$otpLength   = $config->get('otp_length', 6);
			$otpValidity = $config->get('otp_validity', 30);

			$otp    = new Otp($otpValidity, $otpLength);
			$secret = $otp->genSecret($field, $value);
			$code   = $otp->getOtp($secret);

			$sent = $this->sendOtp($value, $type, $code);

			echo new JResponseJson($sent, JText::_($this->text_prefix . '_SENT_OTP_SUCCESS'));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	/**
	 * Check an OTP submitted by the user
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function checkFieldOtp()
	{
		try
		{
			$field = $this->input->getString('field');
			$value = $this->input->getString('value');
			$code  = $this->input->getString('otp');

			$config   = ConfigHelper::getInstance('com_sellacious');

			$otpLength   = $config->get('otp_length', 6);
			$otpValidity = $config->get('otp_validity', 30);

			$otp    = new Otp($otpValidity, $otpLength);
			$secret = $otp->genSecret($field, $value);
			$valid  = $otp->checkOtp($secret, $code);

			if (!$valid)
			{
				throw new Exception(JText::_($this->text_prefix . '_VERIFY_OTP_FAILED'));
			}

			$token = Otp::secureHash($value);
			$data  = array('valid' => $valid, 'token' => $token);

			echo new JResponseJson($data, JText::_($this->text_prefix . '_VERIFY_OTP_SUCCESS'));
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}
	}

	/**
	 * Method to send the OTP to the given email/phone number
	 *
	 * @param   string  $recipient  The recipient email or mobile number
	 * @param   string  $type       The destination type, valid values are: email, tel
	 * @param   string  $code       The OTP code to be sent
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   2.0.0
	 */
	protected function sendOtp($recipient, $type, $code)
	{
		switch ($type)
		{
			case 'email':
				$message = CommunicationHelper::getHandler('email');

				$message->setSubject(JText::_('COM_SELLACIOUS_PREVERIFY_OTP_EMAIL_SUBJECT'));
				$message->setBody(JText::sprintf('COM_SELLACIOUS_PREVERIFY_OTP_EMAIL_BODY', $code));
				$message->addRecipient($recipient);
				$message->send();

				break;

			case 'tel':
				$message = CommunicationHelper::getHandler('text');

				$message->setBody(JText::sprintf('COM_SELLACIOUS_PREVERIFY_OTP_TEXT_BODY', $code));
				$message->addRecipient($recipient);
				$message->send();

				break;

			default:
				throw new Exception(JText::_($this->text_prefix . '_SEND_OTP_INVALID_FIELD'));
		}

		return array('otp' => 999999 - $code);
	}

	/**
	 * Method to check whether the given value is unique or not, supports user email and mobile number
	 *
	 * @param   $userid
	 * @param   $type
	 * @param   $value
	 *
	 * @return  bool
	 *
	 * @since   2.0.0
	 */
	protected function isUnique($userid, $type, $value)
	{
		if ($type == 'email')
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('COUNT(*)')->from('#__users')->where('email = ' . $db->quote($value));

			if ($userid)
			{
				$query->where($db->quoteName('id') . ' <> ' . (int) $userid);
			}

			$dup = (bool) $db->setQuery($query)->loadResult();
		}
		elseif ($type == 'tel')
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('COUNT(*)')->from('#__sellacious_profiles')->where('mobile = ' . $db->quote($value));

			if ($userid)
			{
				$query->where($db->quoteName('user_id') . ' <> ' . (int) $userid);
			}

			$dup = (bool) $db->setQuery($query)->loadResult();
		}
		else
		{
			$dup = false;
		}

		return !$dup;
	}
}
