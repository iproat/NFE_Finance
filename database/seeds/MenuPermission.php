<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuPermission extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menu_permission')->truncate();

        DB::insert("INSERT INTO `menu_permission` (`id`, `role_id`, `menu_id`) VALUES
        (90,	1,	2),
        (91,	1,	3),
        (92,	1,	4),
        (93,	1,	5),
        (94,	1,	6),
        (95,	1,	7),
        (96,	1,	8),
        (97,	1,	9),
        (98,	1,	10),
        (99,	1,	11),
        (100,	1,	12),
        (101,	1,	13),
        (102,	1,	14),
        (103,	1,	15),
        (104,	1,	16),
        (105,	1,	17),
        (106,	1,	22),
        (107,	1,	23),
        (108,	1,	59),
        (109,	1,	60),
        (110,	1,	18),
        (111,	1,	19),
        (112,	1,	20),
        (113,	1,	21),
        (114,	1,	24),
        (115,	1,	50),
        (116,	1,	58),
        (117,	1,	64),
        (118,	1,	90),
        (119,	1,	91),
        (120,	1,	92),
        (121,	1,	94),
        (122,	1,	95),
        (123,	1,	96),
        (124,	1,	98)
        ");
    }
}
