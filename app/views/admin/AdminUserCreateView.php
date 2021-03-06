<?php

class AdminUserCreateView extends View {

	public function title(){
		global $STRINGS;
		return $STRINGS['user'];
	}

	public function menu() {
		return MenuHelper::admin_base_menu('users');
	}

	public function content() {

		global $CONFIG, $STRINGS;

		return '
		<section id="new-user" class="well">
		<form action="'.$CONFIG->wwwroot.'/admin/users/new/create" method="post">
		<label>'.$STRINGS['firstname'].'</label>
		<input type="text" name="firstname">

		<label>'.$STRINGS['lastname'].'</label>
		<input type="text" name="lastname">

		<label>'.$STRINGS['identifier'].'</label>
		<input type="text" name="identifier">

		<label>'.$STRINGS['password'].'</label>
		<input type="password" name="password">

		<label>'.$STRINGS['position'].'</label>
		<input type="text" name="position">

		<label>'.$STRINGS['role'].'</label>
		<select name="role">
		<option>user</option>
		<option>admin</option>
		</select>

		<label></label>
		<button type="submit" class="btn">'.$STRINGS['create:user'].'</button>
		</form>
		</section>';
	}

}
