<?php
namespace Beakman;

use FHC\Last;
use FHC\More;

class Foo
{

    use BarTrait;
    use FirstTrait {
        foo as bar;
        config as protected _config;
    }
}
