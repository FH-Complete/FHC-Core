<?php
interface ICollisionCheck
{
	public function getName();

	public function check($data);
}