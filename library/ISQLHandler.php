<?php

namespace FC\SQL;

interface ISQLHandler
{
    public function sql_instance();
    public function sql_table();
    public function sql_cols();
    public function sql_insertableCols();
    public function sql_modifiableCols();
    public function sql_primaryKey();
}