<?php
interface ICollisionCheck
{
	public function getName();

	public function check($data);

	public function checkAll($kalender_ids);

}