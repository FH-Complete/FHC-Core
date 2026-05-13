<?php

/**
 * Copyright (C) 2026 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 */
trait StdsemFilterTrait
{
	protected function initInteressenten()
	{
		return [
			'children' => [
				'bewebungnichtabgeschickt',
				'bewerbungabgeschickt',
				'zgv',
				'statusbestaetigt',
				'reihungstestnichtangemeldet',
				'reihungstestangemeldet'
			],
			'name' => 'Interessenten'
		];
	}

	protected function initBewebungnichtabgeschickt()
	{
		return [ 'name' => 'Bewerbung nicht abgeschickt' ];
	}

	protected function initBewerbungabgeschickt()
	{
		return [ 'name' => 'Bewerbung abgeschickt, Status unbestätigt' ];
	}

	protected function initZgv()
	{
		return [ 'name' => 'ZGV erfüllt' ];
	}

	protected function initStatusbestaetigt()
	{
		return [
			'children' => [
				'statusbestaetigtrtnichtangemeldet',
				'statusbestaetigtrtangemeldet'
			],
			'name' => 'Status bestätigt'
		];
	}

	protected function initStatusbestaetigtrtnichtangemeldet()
	{
		return [ 'name' => 'Nicht zum Reihungstest angemeldet' ];
	}

	protected function initStatusbestaetigtrtangemeldet()
	{
		return [ 'name' => 'Reihungstest angemeldet' ];
	}

	protected function initReihungstestnichtangemeldet()
	{
		return [ 'name' => 'Nicht zum Reihungstest angemeldet' ];
	}

	protected function initReihungstestangemeldet()
	{
		return [ 'name' => 'Reihungstest angemeldet' ];
	}

	protected function initBewerber()
	{
		return [
			'children' => [
				'bewerberrtnichtangemeldet',
				'bewerberrtangemeldet'
			],
			'name' => 'Bewerber'
		];
	}

	protected function initBewerberrtnichtangemeldet()
	{
		return [ 'name' => 'Nicht zum Reihungstest angemeldet' ];
	}

	protected function initBewerberrtangemeldet()
	{
		return [
			'children' => [
				'bewerberrtangemeldetteilgenommen',
				'bewerberrtangemeldetnichtteilgenommen'
			],
			'name' => 'Reihungstest angemeldet'
		];
	}

	protected function initBewerberrtangemeldetteilgenommen()
	{
		return [ 'name' => 'Teilgenommen' ];
	}

	protected function initBewerberrtangemeldetnichtteilgenommen()
	{
		return [ 'name' => 'Nicht teilgenommen' ];
	}

	protected function initAufgenommen()
	{
		return [ 'name' => 'Aufgenommen' ];
	}

	protected function initWarteliste()
	{
		return [ 'name' => 'Warteliste' ];
	}

	protected function initAbsage()
	{
		return [ 'name' => 'Absage' ];
	}

	protected function initFilterIncoming()
	{
		return [ 'name' => 'Incoming' ];
	}
}
