<?php
/* Copyright (C) 2015 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 *
 * An easy way to keep in track of external processes.
 * Ever wanted to execute a process in php, but you still wanted to have somewhat controll of the process ? Well.. This is a way of doing it.
 * @compability: Linux only. (Windows does not work).
 * @author: Christian Paminger <christian.paminger@technikum-wien.at>
 */
 
class process
{
    private $pid;
    private $command;
    
    public $lastout;
    public $output;
    public $exit;

    public function __construct($cl=false)
    {
        if ($cl != false)
        {
            $this->command = $cl;
            $this->runCom();
        }
    }
    private function runCom()
    {
        $command = 'nohup '.$this->command.' > /dev/null 2>&1 & echo $!';
        $this->lastout=exec($command, $this->output, $this->exit); //exec($command ,$op);
        $this->pid = (int)$this->output[0];
    }

    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function status()
    {
        $command = 'ps -p '.$this->pid;
        exec($command,$op);
        if (!isset($op[1]))
			return false;
        else
			return true;
    }

    public function start()
    {
        if ($this->command != '')
			$this->runCom();
        else 
			return true;
    }

    public function stop()
    {
        $command = 'kill '.$this->pid;
        exec($command);
        if ($this->status() == false)
			return true;
        else
			return false;
    }
}
?>
