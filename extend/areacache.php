<?php

final class areacache {

    public static function getCache($type, $param = "") {
        $type = strtoupper($type[0]) . strtolower(substr($type, 1));
        $function = "get" . $type . "Cache";
        try {
            do {
                if (method_exists(areacache, $function)) {
                    break;
                } else {
                    $error = lang('please_check_your_cache_type');
                    throw new Exception($error);
                }
            } while (0);
        } catch (Exception $e) {
            Exception($e->getMessage(), "", "exception");
        }
        $result = self::$function($param);
        return $result;
    }

    private static function getAreaCache($param) {

        $deep = $param['deep'];
        $cache_file = ROOT_PATH . "extend" . DIRECTORY_SEPARATOR . "area" . DIRECTORY_SEPARATOR . "area_" . $deep . ".php";
        if (file_exists($cache_file) && empty($param['new'])) {
            require ($cache_file);
            return $data;
        }
        $where = array('area_deep' => $deep);
        $order = "area_sort asc";
        $area_mod = model('area');
        $result = $area_mod->getAreaList($where, '*', '', 0, $order);
        $tmp = "<?php \r\n";
        $tmp.= "\$data = array(\r\n";
        if (is_array($result)) {
            foreach ($result as $k => $v) {
                $tmp.= "\tarray(\r\n";
                $tmp.= "\t\t'area_id'=>'" . $v['area_id'] . "',\r\n";
                $tmp.= "\t\t'area_name'=>'" . htmlspecialchars($v['area_name']) . "',\r\n";
                $tmp.= "\t\t'area_region'=>'" . htmlspecialchars($v['area_region']) . "',\r\n";
                $tmp.= "\t\t'area_parent_id'=>'" . $v['area_parent_id'] . "',\r\n";
                $tmp.= "\t\t'area_sort'=>'" . $v['area_sort'] . "',\r\n";
                $tmp.= "\t\t'area_deep'=>'" . $v['area_deep'] . "',\r\n";
                $tmp.= "\t),\r\n";
            }
        }
        $tmp.= ");";
        try {
            $fp = @fopen($cache_file, "wb+");
            if (fwrite($fp, $tmp) === FALSE) {
                $error = lang('please_check_your_system_chmod_area');
                throw new Exception();
            }
            @fclose($fp);
            require ($cache_file);
            return $data;
        } catch (Exception $e) {
            exception($e->getMessage(), "", "exception");
        }
    }

    public static function makeallcache($type) {
        switch ($type) {
            case "area":
                $file_list = read_file_list(ROOT_PATH . "extend" . DIRECTORY_SEPARATOR . "area");
                if (is_array($file_list)) {
                    foreach ($file_list as $v) {
                        @unlink(ROOT_PATH . "extend" . DIRECTORY_SEPARATOR . "area" . DIRECTORY_SEPARATOR . $v);
                    }
                }
                $maxdeep = 1;
            default:
                $where = array('area_deep' => $maxdeep);
                $order = "area_sort asc";
                $area_mod = model('area');
                $result = $area_mod->getAreaList($where, '*', '', 0, $order);
                if (!empty($result)) {
                    $cache_file_area = ROOT_PATH . "extend" . DIRECTORY_SEPARATOR . "area" . DIRECTORY_SEPARATOR . "area_" . $maxdeep . ".php";
                    $tmp = "<?php \r\n";
                    $tmp.= "\$data = array(\r\n";
                    if (is_array($result)) {
                        foreach ($result as $k => $v) {
                            $tmp.= "\tarray(\r\n";
                            $tmp.= "\t\t'area_id'=>'" . $v['area_id'] . "',\r\n";
                            $tmp.= "\t\t'area_name'=>'" . htmlspecialchars($v['area_name']) . "',\r\n";
                            $tmp.= "\t\t'area_region'=>'" . htmlspecialchars($v['area_region']) . "',\r\n";
                            $tmp.= "\t\t'area_parent_id'=>'" . $v['area_parent_id'] . "',\r\n";
                            $tmp.= "\t\t'area_sort'=>'" . $v['area_sort'] . "',\r\n";
                            $tmp.= "\t\t'area_deep'=>'" . $v['area_deep'] . "',\r\n";
                            $tmp.= "\t),\r\n";
                        }
                    }
                    $tmp.= ");";
                    try {
                        $fp = @fopen($cache_file_area, "wb+");
                        if (fwrite($fp, $tmp) === FALSE) {
                            $error = lang('please_check_your_system_chmod_area');
                            throw new Exception();
                        }
                        unset($tmp);
                        @fclose($fp);
                    } catch (Exception $e) {
                        exception($e->getMessage(), "", "exception");
                    }
                }
                ++$maxdeep;
        }
    }

    function del_DirAndFile($dirName) {
        if (is_dir($dirName)) {
            if ($handle = opendir("$dirName")) {
                while (false !== ( $item = readdir($handle) )) {
                    if ($item != "." && $item != "..") {
                        if (is_dir("$dirName/$item")) {
                            del_DirAndFile("$dirName/$item");
                        } else {
                            unlink("$dirName/$item");
                        }
                    }
                }
                closedir($handle);
                rmdir($dirName);
            }
        }
    }

    public static function deleteCacheFile() {
        $dirName = ROOT_PATH . "extend" . DIRECTORY_SEPARATOR . "area";
        if (is_dir($dirName)) {
            if ($handle = opendir("$dirName")) {
                while (false !== ( $item = readdir($handle) )) {
                    if ($item != "." && $item != "..") {
                        if (is_dir("$dirName/$item")) {
                            del_DirAndFile("$dirName/$item");
                        } else {
                            unlink("$dirName/$item");
                        }
                    }
                }
                closedir($handle);
            }
        }
    }

    //自动更新“area_datas.js”
    public static function updateAreaArrayJs() {
        $cache_file = PUBLIC_PATH . DIRECTORY_SEPARATOR . "static" . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR . "area_datas.js";
        $field = "area_parent_id";
        $order = "area_parent_id ASC";
        $group = "area_parent_id";
        $area_mod = model('area');
        $result = $area_mod->getAreaList(array(), $field, $group, 0, $order);

        $tmp = "ds_a = new Array();\n";
        if (is_array($result)) {
            foreach ($result as $k => $v) {
                $tmp.="ds_a[" . $v['area_parent_id'] . "]=[";
                $tmp_sub = "";
                //子地区

                $where = "area_parent_id = '" . $v['area_parent_id'] . "'";
                $order = "area_sort ASC,area_id ASC";
                $result_sub = $area_mod->getAreaList($where, '*', '', 0, $order);
                if (is_array($result_sub)) {
                    foreach ($result_sub as $k_sub => $v_sub) {
                        if ($tmp_sub == "") {
                            $tmp_sub = "['" . $v_sub['area_id'] . "','" . $v_sub['area_name'] . "']";
                        } else {
                            $tmp_sub .= ",['" . $v_sub['area_id'] . "','" . $v_sub['area_name'] . "']";
                        }
                    }
                }
                $tmp.=$tmp_sub;
                $tmp.="];\n";
            }
        }
        $fp = fopen($cache_file, "wb+");
        fwrite($fp, $tmp);
        fclose($fp);
    }

    //自动更新“area_datas.php”
    public static function updateAreaPhp() {
        $cache_file = PUBLIC_PATH . DIRECTORY_SEPARATOR . "static" . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR . "area_datas.php";

        $order = "area_deep asc ,area_sort ASC,area_id ASC";
        $area_mod = model('area');
        $result = $area_mod->getAreaList(array(), '*', '', 0, $order);
        $tmp = "<?php \r\n";
        $tmp.= "\$area_array = array(\r\n";

        if (is_array($result)) {
            foreach ($result as $k => $v) {
                $tmp.= "\t" . $v['area_id'] . " => array ( 'area_name' => '" . $v['area_name'] . "', 'area_parent_id' => '" . $v['area_parent_id'] . "', ),";
            }
        }
        $tmp.= ");";
        $fp = fopen($cache_file, "wb+");
        fwrite($fp, $tmp);
        fclose($fp);
    }

}

?>