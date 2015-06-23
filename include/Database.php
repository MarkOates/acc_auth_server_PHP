<?php


class Database
{
	public static function connect()
	{
		mysql_connect("localhost", "root", "");
		mysql_select_db("acc_login_auth");
	}
};
