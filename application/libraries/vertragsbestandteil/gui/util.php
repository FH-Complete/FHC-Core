<?php

function string2Date($datestring)
{
    $date = DateTimeImmutable::createFromFormat('j.m.Y', $datestring);
    if ($date === false) return null;
    return $date->format('Y-m-d');
}