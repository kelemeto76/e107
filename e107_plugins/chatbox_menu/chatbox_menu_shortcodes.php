<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 chatbox_menu Plugin
 *
*/
if ( ! defined('e107_INIT')) {
	exit;
}


class chatbox_menu_shortcodes extends e_shortcode
{
	/**
	 *  Initializer for chatbox_menu_shortcodes class
	 */
	public function init()
	{
		if ( ! $this->var['user_image'] ) {
			$this->addVars($this->retrieveUserDataByNick($this->var['cb_nick']));
		}

	}


	/**
	 * Returns extended user data from user object
	 *
	 * @param $nick string
	 *
	 * @return array user data
	 */
	protected function retrieveUserDataByNick($nick)
	{

		$tmp = explode('.', $nick);
		$userId = (int)$tmp[0];

		return e107::user($userId);

	}


	/**
	 * Returns user avatar
	 *
	 * @param $parm
	 *
	 * @return \
	 */
	public function sc_cb_avatar($parm = null)
	{
		$this->init();

		$size = $parm['size'] ?: 40;
		$tp = e107::getParser();

		return $tp->toAvatar($this->var, ['h' => $size, 'w' => $size, 'crop' => 'C']);
	}


	/**
	 * Returns user profile link
	 *
	 * @param null $parm
	 *
	 * @return mixed|string
	 */
	public function sc_cb_username($parm = null)
	{

		if (! $this->var['user_id'] && ! $this->var['user_name']) {

			$tmp = explode('.', $this->var['cb_nick']);

			$link = e107::getUrl()->create('user/profile/view', array('id' => $tmp[0], 'name' => $tmp[1]));

			$userName = str_replace('Anonymous', LAN_ANONYMOUS, $tmp[1]);

			return '<a href="' . $link . '">' . $userName . '</a>';

		}

		$this->init();

		$userData = [
			'id'   => $this->var['user_id'],
			'name' => $this->var['user_name'],
		];

		$link = e107::getUrl()->create('user/profile/view', $userData);

		return '<a href="' . $link . '">' . $this->var['user_name'] . '</a>';
	}


	/**
	 * Returns relative timestamp
	 *
	 * @param null $parm
	 *
	 * @return string
	 */
	public function sc_cb_timedate($parm = null)
	{

		return e107::getDate()
			->convert_date($this->var['cb_datestamp'], 'relative');
	}


	/**
	 * Returns chatbox message
	 *
	 * @param null $parm
	 *
	 * @return string
	 */
	public function sc_cb_message($parm = null)
	{
		$this->init();

		if ($this->var['cb_blocked']) {
			return CHATBOX_L6;
		}

		$pref = e107::getPref();
		$emotes_active = $pref['cb_emote'] ? 'USER_BODY, emotes_on'
			: 'USER_BODY, emotes_off';

		$cb_message = e107::getParser()
			->toHTML($this->var['cb_message'], false, $emotes_active,
				$this->var['user_id'], $pref['menu_wordwrap']);

		return $cb_message;
	}


	/**
	 * Returns bullet image
	 *
	 * @param null $parm
	 *
	 * @return string
	 */
	public function sc_cb_bullet($parm = null)
	{
		$bullet = '';

		if (defined('BULLET')) {
			$bullet =
				'<img src="' . THEME_ABS . 'images/' . BULLET . '" alt="" class="icon" />';
		} elseif (file_exists(THEME . 'images/bullet2.gif')) {
			$bullet =
				'<img src="' . THEME_ABS . 'images/bullet2.gif" alt="" class="icon" />';
		}

		return $bullet;
	}


	/**
	 * Returns moderator options
	 *
	 * @param null $parm
	 *
	 * @return string
	 */
	public function sc_cb_mod($parm = null)
	{
		$frm = e107::getForm();
		$modControls = '';

		if (CB_MOD) {
			$id = $this->var['cb_id'];

			$modControls .= "<span class='checkbox'>";

			$modControls .= $frm->checkbox('delete[' . $id . ']', 1, false,
				['inline' => true, 'label' => LAN_DELETE]);

			if ($this->var['cb_blocked']) {
				$modControls .= $frm->checkbox('unblock[' . $id . ']', 1, false,
					['inline' => true, 'label' => CHATBOX_L7]);
			} else {
				$modControls .= $frm->checkbox('block[' . $id . ']', 1, false,
					['inline' => true, 'label' => CHATBOX_L9]);
			}

			$modControls .= '</span>';
		}

		return $modControls;
	}


	/**
	 * Returns moderator block message
	 *
	 * @param null $parm
	 *
	 * @return string
	 */
	public function sc_cb_blocked($parm = null)
	{
		return $this->var['cb_blocked'] ? '<span class="label label-warning">' . CHATBOX_L25 . '</span>' : '';
	}

}