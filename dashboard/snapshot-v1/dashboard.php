<?php
// Désactiver le rapport d'erreurs
error_reporting(0);

/**
 * @param $row
 */
function displayRowMetrics($filename, $row, &$data_html, $level = 0) {
    $html = '';
    if(isSet($row["#VALUE"])) {
        $class = '';
        //$class = ($level > 2) ? 'style="display: none;"' : '';
        $html .= "    <tr ".$class.">\n";
        $html .= "        <td style=\"padding-left: ".(20 * ($level - 1))."px;\">" . $filename . "</td>\n";
        foreach ($row["#VALUE"] as $key => $val) {
            $html .= "        <td align=\"center\">" . str_pad("&nbsp;", $level).$val . "</td>\n";
        }
        $html .= "    </tr>\n";
        $data_html .= $html;
    }
    foreach($row as $key => $val) {
        if($key != '#VALUE')  $html .= displayRowMetrics($key, $val, $data_html, ($level + 1));
    }
    return $level;
}

/**
 * @param $id
 * @return mixed
 */
function sanitizeID($id) {
    return str_replace("/", "-", str_replace(".", "-", str_replace(" ", "-", $id)));
}

/**
 * @param $table
 * @param $key
 * @param $value
 */
function table_set($keys, $value, &$table, $level = 0) {
    if(is_array($keys) && sizeof($keys) > 0) {
        $key = array_shift($keys);
        return array("#VALUE" => $value, $key => table_set($keys, $value, $table[$key], ($level + 1)));
    }
    $table = array("#VALUE" => $value);
    return $value;
}

/**
 * Returns the names of files contained in a directory and all subdirectories.
 *
 * @param string       $dir  Path
 * @param false|string $type Extension Value of files that we want to search.
 *
 * @return array    array list of all files.
 * @access public
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
function ListFiles($dir, $type = false)
{
    $files = Array();
    if ($dh = @opendir($dir)) {
        $inner_files = Array();
        while($file = readdir($dh)) {
            if ($file != "." && $file != ".." && $file[0] != '.') {
                if (is_dir($dir . "/" . $file)) {
                    $inner_files = ListFiles($dir . "/" . $file, $type);
                    if (is_array($inner_files)) {
                        $files = array_merge($files, $inner_files);
                    }
                } else {
                    if ($type) { //validate the type
                        $fileParts = explode('.', $file);
                        if (is_array($fileParts)) {
                            $fileType = array_pop($fileParts);
                            //check whether the filetypes were passed as an array or string
                            if (is_array($type)) {
                                if (in_array($fileType, $type)) {
                                    array_push($files, $dir . "/" . $file);
                                }
                            } else {
                                if ($fileType == $type) {
                                    array_push($files, $dir . "/" . $file);
                                }
                            }
                        }
                    } else {
                        array_push($files, $dir . "/" . $file);
                    }
                }
            }
        }
        closedir($dh);
    }

    return $files;
}

parse_str(implode('&', array_slice($argv, 1)), $_GET);

$dir_to_analyze = $_GET['pathlog']; // "build/logs/php/";
$src            = $_GET['pathsrc']; // "src";
$items          = ListFiles($dir_to_analyze);

$html = "
    <html>
        <head>
            <script type=\"text/javascript\" src=\"https://code.jquery.com/jquery-1.11.3.min.js\"></script>
            <script type=\"text/javascript\" src=\"https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js\"></script>
            <script type=\"text/javascript\" src=\"https://code.jquery.com/ui/1.11.3/jquery-ui.min.js\"></script>
            <script  style=\"display:none\" type=\"text/javascript\">
              // (c) 2010 jdbartlett, MIT license http://redopop.com/loupe/
              (function(a){a.fn.loupe=function(b){var c=a.extend({loupe:\"loupe\",width:200,height:150},b||{});return this.length?this.each(function(){var j=a(this),g,k,f=j.is(\"img\")?j:j.find(\"img:first\"),e,h=function(){k.hide()},i;if(j.data(\"loupe\")!=null){return j.data(\"loupe\",b)}e=function(p){var o=f.offset(),q=f.outerWidth(),m=f.outerHeight(),l=c.width/2,n=c.height/2;if(!j.data(\"loupe\")||p.pageX>q+o.left+10||p.pageX<o.left-10||p.pageY>m+o.top+10||p.pageY<o.top-10){return h()}i=i?clearTimeout(i):0;k.show().css({left:p.pageX-l,top:p.pageY-n});g.css({left:-(((p.pageX-o.left)/q)*g.width()-l)|0,top:-(((p.pageY-o.top)/m)*g.height()-n)|0})};k=a(\"<div />\").addClass(c.loupe).css({width:c.width,height:c.height,position:\"absolute\",overflow:\"hidden\"}).append(g=a(\"<img />\").attr(\"src\",j.attr(j.is(\"img\")?\"src\":\"href\")).css(\"position\",\"absolute\")).mousemove(e).hide().appendTo(\"body\");j.data(\"loupe\",true).mouseenter(e).mouseout(function(){i=setTimeout(h,10)})}):this}}(jQuery));
                      //-->
            </script>
            <style>
                * { font-family: Verdana; font-size: 12px; }
                .ui-tabs-panel table a { text-decoration: none; color: #FF3333; }
                table { background-color: #e8be74; }
                table tr.even { background-color: #CCCCCC; }
                table tr td { padding-left: 3px; padding-right: 3px; background-color: #FFFFFF; }
                table tr td.even { font-weight: bold; background-color: #CCCCCC; }
                table tr td.title { font-weight: bold; text-transform: uppercase; }

                span.description {  position: absolute; margin-top: 5px; margin-left: -115px; padding: 5px; display: none; width: 300px; background-color: #ECECEC; border: 1px solid black; text-align: left; font-weight: normal; }
                span.title { text-transform: uppercase; font-weight: bold; margin-bottom: 5px; display: block;  }
                table tr th:hover > span.description { display: block; }
                div.graphics { width: 95%; margin: auto; margin-bottom: 15px; }
                div.graphics div.dependencies, div.graphics div.pyramid { text-align: center; display: inline-block; width: 48%; }
                .green { background-color: #33CC22; }
                .orange { background-color: #FF6633; }
                .red { background-color: #FF2244; }
                .text { padding: 10px; font-size: 1.1em; text-align: center; }

                .icon {
                  height: 91px;
                  overflow: hidden;
                  margin: auto;
                  box-shadow: rgba(0,0,0,.5) 0 0 4px;
                  line-height: 0;
                  transition-duration: 0.3s;
                }
                .icon img {
                  height: 91px;
                  transition-duration: 0.3s;
                  transition-property: transform;/* just for candy: */
                }
            </style>

            <link rel=\"stylesheet\"  href=\"https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css\">
            <link rel=\"stylesheet\"  href=\"https://code.jquery.com/ui/1.11.3/themes/pepper-grinder/jquery-ui.css\">

            <script type=\"text/javascript\">
                $(document).ready(function() {
                    $('#tabs').tabs();
                    $('a.fancyb').fancybox({
                          'hideOnContentClick': true
                    });
                    $('.zoom').loupe({
                      width: 900, // width of magnifier
                      height: 600, // height of magnifier
                      loupe: 'loupe' // css class for magnifier
                    });
                });
            </script>
        </head>
        <body>
  ";

$idx_file_loc = 0;
$data = [];
$data["LOC"] = [];
$data["PHPMD"] = [];
$data["PHPCS"] = [];
$data["PHPMD"]["COLS"] = [];
$data["PDEPEND"] = [];
$data["CPD"] = [];
$data["METRICS"] = [];
$data["ENCODE"] = [];
$data["SECU_PARSE"] = [];

foreach($items as $item) {
    $filename = $item;
    $item = basename($item);
    $content_file = trim(file_get_contents($filename));

    if(!is_dir($filename)) {
        // +--------+
        // | PHPLOC |
        // +--------+
        if(strpos($item, 'phploc') !== false && substr($item, -3) == 'csv') {
            $h = fopen($filename, "rt");
            if($h !== false) {
                $data["LOC"]["files"][$idx_file_loc] = $item;
                $idx_line = 0;
                $key = [];
                while($loc_data = fgetcsv($h)) {
                    foreach($loc_data as $idx => $d) {
                        if($idx_line == 0) {
                            $key[$idx] = $d;
                        } else {
                            $data["LOC"]["data"][$key[$idx]][] = $d;
                        }
                    }
                    $idx_line ++;
                }
                fclose($h);
                $idx_file_loc ++;
            }
        }

        // +------------+
        // | PHPMETRICS |
        // +------------+
        if(strpos($item, 'phpmetrics') !== false && substr($item, -4) == 'json') {
            if (!empty($content_file)) {
                $json = json_decode($content_file);
                $item_name = substr($item, 0, -5);
                if (!isSet($data["METRICS"][$item_name])) $data["METRICS"][$item_name] = [];

                $dmetrics = [];
                foreach ($json as $iti => $jfile) {
                    //echo $jfile->filename."\n";
                    $value = array(
                        "loc" => $jfile->loc,
                        "lcom" => $jfile->lcom,
                        "cyclomaticComplexity" => $jfile->cyclomaticComplexity,
                        "maintainabilityIndex" => $jfile->maintainabilityIndex,
                        "instability" => $jfile->instability,
                        "volume" => $jfile->volume,
                    );

                    table_set(explode("/", $jfile->filename), $value, $dmetrics);
                }
                $data["METRICS"][$item_name] = array_merge_recursive($data["METRICS"][$item_name], $dmetrics);
                //ksort($data["METRICS"][$item_name]);
            }
        }
        if(strpos($item, 'phpmetrics') !== false && substr($item, -3) == 'xml') {
            if (!empty($content_file)) {
                $xml = new SimpleXMLElement($content_file);
                $dmetrics = [];
                $item_name = substr($item, 0, -4);
                if (!isSet($data["METRICS"][$item_name])) $data["METRICS"][$item_name] = [];
                foreach ($xml->modules->module as $module) {
                    $attr = $module->attributes();
                    $value = array(
                        "loc" => $attr["loc"]->__toString(),
                        "lcom" => $attr["lcom"]->__toString(),
                        "cyclomaticComplexity" => $attr["cyclomaticComplexity"]->__toString(),
                        "maintainabilityIndex" => $attr["maintainabilityIndex"]->__toString(),
                        "instability" => $attr["instability"]->__toString(),
                        "volume" => $attr["volume"]->__toString(),
                    );
                    table_set(explode("/", $attr["namespace"]->__toString()), $value, $dmetrics);
                }
                $data["METRICS"][$item_name] = array_merge_recursive($data["METRICS"][$item_name], $dmetrics);
                //ksort($data["METRICS"][$item_name]);
            }
        }

        // +---------+
        // | PDEPEND |
        // +---------+
        if(strpos($item, 'pdepend') !== false && substr($item, -3) == 'xml') {
            if (!empty($content_file)) {
                $xml = new SimpleXMLElement($content_file);
                $data["PDEPEND"][$item] = [];
                foreach ($xml->Packages->Package as $package) {
                    $attr = $package->attributes();
                    $pname = current($attr["name"]);
                    $data["PDEPEND"][$item][$pname] = [];
                    $data["PDEPEND"][$item][$pname]["Stats"] = [];
                    foreach ($package->Stats->children() as $stat_name => $stat_value) {
                        $data["PDEPEND"][$item][$pname]["Stats"][$stat_name] = $stat_value->__toString();
                    }
                }
            }
        }

        // +-----+
        // | CPD |
        // +-----+
        if(strpos($item, 'php-cpd') !== false && substr($item, -3) == 'xml') {
            $data["CPD"][$item] = array("TOTAL" => 0, "DOUBLONS" => []);

            if (!empty($content_file)) {
                $xml = new SimpleXMLElement($content_file);
                foreach ($xml->duplication as $doublon) {
                    $attr = $doublon->attributes();
                    $nb_lines = $attr["lines"]->__toString();
                    $data["CPD"][$item]["TOTAL"] += $nb_lines;

                    $files = [];
                    foreach ($doublon->file as $fdoublon) {
                        $attr_fdoublon = $fdoublon->attributes();
                        $files[] = array(
                            "path" => $attr_fdoublon["path"]->__toString(),
                            "line" => $attr_fdoublon["line"]->__toString(),
                        );
                    }

                    $data["CPD"][$item]["DOUBLONS"][] = array(
                        "nb_lines" => $nb_lines,
                        "code" => $doublon->codefragment->__toString(),
                        "files" => $files
                    );
                }
            }
        }

        // +-------+
        // | PHPMD |
        // +-------+
        if(strpos($item, 'phpmd') !== false && substr($item, -3) == 'xml') {
            $data["PHPMD"][$item] = [];
            if (!empty($content_file)) {
                $xml = new SimpleXMLElement($content_file);

                foreach ($xml->file as $file) {
                    $itemFile = current($file->attributes()["name"]);
                    $data["PHPMD"][$item][$itemFile] = [];
                    foreach ($file->violation as $v) {
                        $attr = $v->attributes();

                        $key = current($attr["ruleset"]);
                        $data["PHPMD"]["COLS"][$item][$key] = '';
                        if (!isSet($data["PHPMD"][$item][$itemFile][$key])) $data["PHPMD"][$item][$itemFile][$key]["cpt"] = 0;
                        $data["PHPMD"][$item][$itemFile][$key]["cpt"]++;

                        $rule_key = current($attr["rule"]);
                        $begin_key = current($attr["beginline"]);
                        $end_key = current($attr["endline"]);
                        $priority_key = current($attr["priority"]);
                        $data["PHPMD"][$item][$itemFile][$key]["errors"][] = array(
                            "begin_line" => $begin_key,
                            "end_line" => $end_key,
                            "rule" => $rule_key,
                            "priority" => $priority_key,
                            "message" => $v->__toString()
                        );
                    }
                }
            }
        }

//    // +--------+
//    // | PHPCS |
//    // +--------+
//    if(strpos($item, 'cs-symfony2') !== false && substr($item, -3) == 'csv') {
//      $hh = fopen($filename, "rt"):
//      if($hh !== false) {
//        $idx_line = 0;
//        $key = [];
//        while($data = fgetcsv($hh)) {
//          foreach($data as $idx => $d) {
//            if ($idx_line === 0) {
//              $key[$idx] = $d;
//            } else {
//              $data["PHPCS"][$item][ $key[$idx] ][] = $d;
//            }
//          }
//          $idx_line ++;
//        }
//        fclose($hh);
//      }
//    }

        // +------------+
        // | ENCODE |
        // +------------+
        if(strpos($item, 'encoding') !== false && substr($item, -3) == 'txt') {
            $reading = fopen($filename, "rt");
            if($reading !== false) {
                $total_line = 0;
                while (!feof($reading)) {
                    $line = fgets($reading);
                    list($file, $encode_type) = explode(';', $line);
                    list($charset, $encode_type) = explode('=', $encode_type);
                    $data["ENCODE"][$item][trim($encode_type)][] = $file;
                }
                fclose($reading);
            }
        }

        // +-----+
        // | PARSE |
        // +-----+
        if(strpos($item, 'security-parse') !== false && substr($item, -3) == 'xml') {
            $t = preg_match('/<results.*>(.*?)<\/results>/s', file_get_contents($filename), $matches);
            if(null !== $matches[0]) {
                $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . $matches[0]);
                $data["SECU_PARSE"][$item]["TOTAL"] = 0;
                foreach ($xml->issue as $doublon) {
                    $data["SECU_PARSE"][$item]['RULES'][$doublon->type->__toString()][] = array(
                        "file" => $doublon->file->__toString(),
                        "description" => $doublon->description->__toString(),
                        "line" => $doublon->line->__toString(),
                        "source" => $doublon->source->__toString()
                    );
                    $data["SECU_PARSE"][$item]["TOTAL"]++;
                }
            }
        }

        // +-----+
        // | UML |
        // +-----+
        if(strpos($item, 'da-uml') !== false && substr($item, -3) == 'svg') {
            $data["UML_DA"][dirname($filename)][] = $filename;
        }
    }
}

$html .= "<div id=\"tabs\">
            <ul>\n";
if(sizeof($data["LOC"]) > 0)              $html .= "<li><a href=\"#tab-loc\">PHPLOC</a></li>\n";
if(sizeof($data["PHPMD"]["COLS"]) > 0)    $html .= "<li><a href=\"#tab-phpmd\">PHPMD</a></li>\n";
if(sizeof($data["PDEPEND"]) > 0)          $html .= "<li><a href=\"#tab-pdepend\">PDEPEND</a></li>\n";
if(sizeof($data["CPD"]) > 0)              $html .= "<li><a href=\"#tab-cpd\">CPD</a></li>\n";
if(sizeof($data["ENCODE"]) > 0)           $html .= "<li><a href=\"#tab-encode\">ENCODE</a></li>\n";
if(sizeof($data["SECU_PARSE"]) > 0)       $html .= "<li><a href=\"#tab-parse\">PARSE SECU.</a></li>\n";
if(sizeof($data["UML_DA"]) > 0)              $html .= "<li><a href=\"#tab-umlda\">Package Diagr.</a></li>\n";
if(sizeof($data["PHPCS"]) > 0)            $html .= "<li><a href=\"#tab-phpcs\">PHPCS</a></li>\n";
if(sizeof($data["METRICS"]) > 0)          {
    $html .= "<li><a href=\"#tab-phpmetrics\">PHPMETRICS-resume</a></li>\n";
    $html .= "<li><a href=\"#tab-phpmetrics-dashboard\">PHPMETRICS-dashboard</a></li>\n";
}
$html .= "<li><a href=\"#tab-phpunit-coverage\">PHPunit Coverage</a></li>\n";
$html .= "</ul>\n";

// +--------+
// | PHPCS |
// +--------+
if(sizeof($data["PHPCS"]) > 0) {
    $html .= "<div id=\"tab-phpcs\">\n";

    $html .= "</div>\n";
}

// +--------+
// | UML |
// +--------+
if(sizeof($data["UML_DA"]) > 0) {
    $html .= "<div id=\"tab-umlda\">\n";
    //
    foreach($data["UML_DA"] as $source) {
        asort($source);
        //
        $html .= "<table align=\"center\">\n";
        $html .= "<tr>\n";
        $html .= "  <td class=\"title\">Sommaire</td>\n";
        $html .= "  <td class=\"title\">Icon</td>\n";
        $html .= "</tr>\n";
        foreach ($source as $k => $filename) {
            $html .= "<tr id='summary'>\n";
            $html .= "  <td><a href='#top-${k}'>" . basename($filename, '.svg') . "</a></td>";
            $html .= "  <td><img class='icon' src='data:image/svg+xml;base64,".base64_encode(file_get_contents($filename))."' height = '45%' /></td>\n";
            $html .= "</tr>\n";
        }
        $html .= "</table><br/><br/>\n";
        //
        $html .= "<table align=\"center\">\n";
        foreach ($source as $k => $filename) {
            $html .= "<tr>\n";
            $html .= "  <td id='top-${k}'>" . basename($filename, '.svg') . " | <a href='#summary'>TOP</a></td>";
            $html .= "</tr>\n";
            $html .= "<tr>\n";
            $html .= "  <td><img class='zoom' src='data:image/svg+xml;base64,".base64_encode(file_get_contents($filename))."' height = '45%' /></td>\n";
            $html .= "</tr>\n";
        }
        $html .= "</table><br/>\n";
    }
    //
    $html .= "</div>\n";
}

// +--------+
// | SECU_PARS |
// +--------+
if(sizeof($data["SECU_PARSE"]) > 0) {
    $html .= "<div id=\"tab-parse\">\n";
    foreach ($data["SECU_PARSE"] as $source => $all) {
        $html .= "<h2>" . $source . "</h2>\n";
        $html .= "<div class=\"text\">Found ".$all['TOTAL']." errors</div>\n";
        foreach($all['RULES'] as $type => $doublon) {
            $html .= "<table align=\"center\" width=\"75%\">\n";
            $html .= "    <tr>\n";
            $html .= "        <th width=\"25%\" align=\"center\">Type</th>\n";
            $html .= "        <th>Files</th>\n";
            $html .= "        <th width=\"10%\" align=\"center\">Line in file</th>\n";
            $html .= "        <th width=\"10%\" align=\"center\">Source code</th>\n";
            $html .= "    </tr>\n";
            foreach($doublon as $idx => $f) {
                $html .= "    <tr>\n";
                $rowspan = " rowspan=\"" . sizeof($doublon) . "\"";
                if($idx == 0) {
                    $html .= "        <td align=\"center\"" . $rowspan . ">" . $type . "</td>\n";
                }
                $html .= "        <td>" . $f["file"] . "</td>\n";
                $html .= "        <td align=\"center\">" . $f["line"] . "</td>\n";
                $id = sanitizeID($type."-".$idx_doublon);
                $html .= "        <td align=\"center\"><a class=\"fancyb\" href=\"#".$id."\">Show</a><div style=\"display: none;\"><div id=\"".$id."\" class=\"code\">";

                $html .= "<table>\n";
                $html .= "<tr>\n";
                $html .= "<td>Description</td>\n";
                $html .= "<td>" . highlight_string($f["description"], true) . "</td>\n";
                $html .= "</tr>\n";
                $html .= "<tr>\n";
                $html .= "<td>Code</td>\n";
                $html .= "<td>" . highlight_string($f["source"], true) . "</td>\n";
                $html .= "</tr>\n";
                $html .= "</table>\n";

                $html .= "         </div></div></td>\n";
                $html .= "    </tr>\n";
            }
            $html .= "</table>\n";
        }
    }
    $html .= "</div>\n";
}

// +--------+
// | ENCODE |
// +--------+
if(sizeof($data["ENCODE"]) > 0) {
    $html .= "<div id=\"tab-encode\">\n";
    //
    foreach($data["ENCODE"] as $source => $vals) {
        $html .= "<h2>" . $source . "</h2>\n";
        $html .= "<table align=\"center\">\n";
        $html .= "<tr>\n";
        $html .= "<td></td>\n";
        $html .= "<td class=\"title\">" . $source . "</td>\n";
        $html .= "</tr>\n";
        foreach ($vals as $encode => $files) {
            $html .= "<tr>\n";
            $html .= "  <td><strong>" . $encode . "</strong></td>\n";
            $html .= "  <td>\n";
            $html .= "      <a class=\"fancyb\" href=\"#detailsencode-" . sanitizeID($source . "-" . $encode) . "\">" . count($files) . "</a>\n";
            $html .= "  </td>\n";
            $html .= "</tr>\n";
        }
        $html .= "</table><br/>\n";
        foreach ($vals as $encode => $files) {
            $html .= "<div style=\"display: none;\"><div id=\"detailsencode-" . sanitizeID($source . "-" . $encode) . "\">\n";
            $html .= "<h2>" . $source . "</h2>";
            $html .= "<table>\n";
            $html .= "<tr>\n";
            $html .= "<th>FILES</th>\n";
            $html .= "</tr>\n";
            foreach ($files as $val) {
                $html .= "<tr>\n";
                $html .= "<td>" . $val . "</td>\n";
                $html .= "</tr>\n";
            }
            $html .= "</table>\n";
            $html .= "</div></div>\n";
        }
    }
    //
    $html .= "</div>\n";
}

// +--------+
// | PHPLOC |
// +--------+
if(sizeof($data["LOC"]) > 0) {
    $html .= "<div id=\"tab-loc\">\n";
    $html .= "<table align=\"center\">\n";
    $html .= "<tr>\n";
    $html .= "<td></td>\n";
    foreach($data["LOC"]["files"] as $file) {
        $html .= "<td class=\"title\">".$file."</td>\n";
    }
    $html .= "</tr>\n";

    foreach($data["LOC"]["data"] as $attr => $vals) {
        $html .= "<tr>\n";
        $html .= "<td><strong>".$attr."</strong></td>\n";
        foreach ($vals as $val) {
            $html .= "<td>".$val."</td>\n";
        }
        $html .= "</tr>\n";
    }
    $html .= "</table>\n";
    $html .= "</div>\n";
}

// +--------+
// | PHPMD |
// +--------+
if(sizeof($data["PHPMD"]["COLS"]) > 0) {
    $html .= "<div id=\"tab-phpmd\">\n";
    foreach ($data["PHPMD"]["COLS"] as $file => $cols) {
        $html .= "<h2>" . $file . "</h2>\n";
        $html .= "<table width=\"95%\" align=\"center\">\n";
        $html .= "<tr>\n";
        $html .= "<th></th>\n";
        foreach ($cols as $col_name => $val) {
            $html .= "<th width=\"15%\">" . $col_name . "</th>\n";
        }
        $html .= "</tr>\n";

        $num_row = 0;
        foreach ($data["PHPMD"][$file] as $item => $info) {
            $class = "odd";
            if ($num_row % 2) {
                $class = "even";
            }
            $html .= "<tr class=\"" . $class . "\">";
            $html .= "<td>" . $item . "</td>\n";
            foreach ($cols as $col_name => $val) {
                $value = "-";
                if (isSet($info[$col_name])) {
                    $value = "<a class=\"fancyb\" href=\"#details-" . sanitizeID($file . "-" . $item . "-" . $col_name) . "\">" . $info[$col_name]["cpt"] . "</a>";
                }
                $html .= "<td align=\"center\">" . $value . "</td>\n";
            }
            $html .= "</tr>\n";
            $num_row++;
        }
        $html .= "</table><br/>\n";

        foreach ($data["PHPMD"]["COLS"] as $file => $cols) {
            foreach ($data["PHPMD"][$file] as $f => $err) {
                foreach ($err as $rule => $details) {
                    $html .= "<div style=\"display: none;\"><div id=\"details-" . sanitizeID($file . "-" . $f . "-" . $rule) . "\">\n";
                    $html .= "<h2>" . $f . " - " . $rule . "</h2>";
                    $html .= "<table>\n";
                    $html .= "<tr>\n";
                    $html .= "<th>RULE</th>\n";
                    $html .= "<th>BEGIN LINE</th>\n";
                    $html .= "<th>END LINE</th>\n";
                    $html .= "<th>PRIORITY</th>\n";
                    $html .= "<th>DESC</th>\n";
                    $html .= "</tr>\n";
                    foreach ($details["errors"] as $val) {
                        $html .= "<tr>\n";
                        $html .= "<td>" . $val["rule"] . "</td>\n";
                        $html .= "<td>" . $val["begin_line"] . "</td>\n";
                        $html .= "<td>" . $val["end_line"] . "</td>\n";
                        $html .= "<td>" . $val["priority"] . "</td>\n";
                        $html .= "<td>" . $val["message"] . "</td>\n";
                        $html .= "</tr>\n";
                    }
                    $html .= "</table>\n";
                    $html .= "</div></div>\n";
                }
            }
        }
    }
    $html .= "</div>\n";
}

// +---------+
// | PDEPEND |
// +---------+
if(sizeof($data["PDEPEND"]) > 0) {
    $html .= "<div id=\"tab-pdepend\">\n";
    foreach ($data["PDEPEND"] as $file => $packages) {
        $nb_packages = sizeof($packages);
        $html .= "<h2>" . $file . " (" . $nb_packages . " package(s))</h2>\n";

        $html .= "<div class=\"graphics\">\n";
        $svg = file_get_contents($dir_to_analyze."/pdepend/dependencies.svg");
        $html .= "  <div class=\"dependencies\"><img src=\"data:image/svg+xml;base64,".base64_encode($svg)."\" height=\"45%\" /></div>";
        $svg = file_get_contents($dir_to_analyze."/pdepend/overview-pyramid.svg");
        $html .= "  <div class=\"pyramid\"><img src=\"data:image/svg+xml;base64,".base64_encode($svg)."\" height=\"45%\" /></div>";
        $html .= "</div>\n";

        if ($nb_packages > 0) {
            $html .= "<table align=\"center\" width=\"95%\">\n";
            $html .= "    <tr>\n";
            $html .= "        <th>Package</th>\n";
            $html .= "        <th width=\"10%\">Total classes (?)<span class=\"description\"><span class=\"title\">Total classes</span>The number of concrete and abstract classes (and interfaces) in the package is an indicator of the extensibility of the package.</span></th>\n";
            $html .= "        <th width=\"10%\">Concrete classes (?)<span class=\"description\"><span class=\"title\">Concrete classes</span>The number of concrete classes in the package is an indicator of the extensibility of the package.</span></th>\n";
            $html .= "        <th width=\"10%\">Abstract classes (?)<span class=\"description\"><span class=\"title\">Abstract classes</span>The number of abstract classes (and interfaces) in the package is an indicator of the extensibility of the package.</span></th>\n";
            $html .= "        <th width=\"5%\">Ca (?)<span class=\"description\"><span class=\"title\">Afferent Couplings</span>The number of other packages that depend upon classes within the package is an indicator of the package's responsibility. </span></th>\n";
            $html .= "        <th width=\"5%\">Ce (?)<span class=\"description\"><span class=\"title\">Efferent Couplings</span>The number of other packages that the classes in the package depend upon is an indicator of the package's independence.</span></th>\n";
            $html .= "        <th width=\"5%\">A (?)<span class=\"description\"><span class=\"title\">Abstractness</span>The ratio of the number of abstract classes (and interfaces) in the analyzed package to the total number of classes in the analyzed package.<br/><br/>The range for this metric is 0 to 1, with A=0 indicating a completely concrete package and A=1 indicating a completely abstract package. </span></th>\n";
            $html .= "        <th width=\"5%\">I (?)<span class=\"description\"><span class=\"title\">Instability</span>The ratio of efferent coupling (Ce) to total coupling (Ce + Ca) such that I = Ce / (Ce + Ca). This metric is an indicator of the package's resilience to change.<br/><br/>The range for this metric is 0 to 1, with I=0 indicating a completely stable package and I=1 indicating a completely instable package. </span></th>\n";
            $html .= "        <th width=\"5%\">D (?)<span class=\"description\"><span class=\"title\">Distance from the Main Sequence</span>The perpendicular distance of a package from the idealized line A + I = 1. This metric is an indicator of the package's balance between abstractness and stability.<br/><br/>A package squarely on the main sequence is optimally balanced with respect to its abstractness and stability. Ideal packages are either completely abstract and stable (x=0, y=1) or completely concrete and instable (x=1, y=0).<br/><br/>The range for this metric is 0 to 1, with D=0 indicating a package that is coincident with the main sequence and D=1 indicating a package that is as far from the main sequence as possible.</span></th>\n";
            $html .= "    </tr>\n";
            foreach ($packages as $pname => $pvalue) {
                $html .= "    <tr>\n";
                $html .= "        <td>" . $pname . "</td>\n";
                $html .= "        <td align=\"center\">" . $pvalue["Stats"]["TotalClasses"] . "</td>\n";
                $html .= "        <td align=\"center\">" . $pvalue["Stats"]["ConcreteClasses"] . "</td>\n";
                $html .= "        <td align=\"center\">" . $pvalue["Stats"]["AbstractClasses"] . "</td>\n";
                $html .= "        <td align=\"center\">" . $pvalue["Stats"]["Ca"] . "</td>\n";
                $html .= "        <td align=\"center\">" . $pvalue["Stats"]["Ce"] . "</td>\n";
                $html .= "        <td align=\"center\">" . $pvalue["Stats"]["A"] . "</td>\n";
                $html .= "        <td align=\"center\">" . $pvalue["Stats"]["I"] . "</td>\n";
                $class = "green";
                if($pvalue["Stats"]["D"] >= 0.15) $class = "orange";
                if($pvalue["Stats"]["D"] >= 0.5) $class = "red";
                $html .= "        <td align=\"center\" class=\"".$class."\">" . $pvalue["Stats"]["D"] . "</td>\n";
                $html .= "    </tr>\n";
            }
            $html .= "</table>\n";
        }
    }
    $html .= "</div>\n";
}

// +-----+
// | CPD |
// +-----+
if(sizeof($data["CPD"]) > 0) {
    $html .= "<div id=\"tab-cpd\">\n";
    foreach ($data["CPD"] as $file => $doublons) {
        $html .= "<h2>".$file."</h2>\n";
        $html .= "<div class=\"text\">Found ".sizeof($doublons["DOUBLONS"])." exact clones with ".$doublons["TOTAL"]." duplicated lines.</div>\n";
        foreach($doublons["DOUBLONS"] as $idx_doublon => $doublon) {
            $html .= "<table align=\"center\" width=\"75%\">\n";
            $html .= "    <tr>\n";
            $html .= "        <th width=\"10%\" align=\"center\"># Duplicate lines</th>\n";
            $html .= "        <th>Files</th>\n";
            $html .= "        <th width=\"10%\" align=\"center\">Line in file</th>\n";
            $html .= "        <th width=\"10%\" align=\"center\">Duplicate code</th>\n";
            $html .= "    </tr>\n";
            foreach($doublon["files"] as $idx => $f) {
                $html .= "    <tr>\n";
                $rowspan = " rowspan=\"" . sizeof($doublon["files"]) . "\"";
                if($idx == 0) {
                    $html .= "        <td align=\"center\"" . $rowspan . ">" . $doublon["nb_lines"] . "</td>\n";
                }
                $html .= "        <td>" . $f["path"] . "</td>\n";
                $html .= "        <td align=\"center\">" . $f["line"] . "</td>\n";
                if($idx == 0) {
                    $id = sanitizeID($file."-".$idx_doublon);
                    $html .= "        <td align=\"center\"" . $rowspan . "><a class=\"fancyb\" href=\"#".$id."\">Show</a><div style=\"display: none;\"><div id=\"".$id."\" class=\"code\">".highlight_string($doublon["code"], true)."</div></div></td>\n";
                }
                $html .= "    </tr>\n";
            }
            $html .= "</table>\n";
        }
    }
    $html .= "</div>\n";
}

// +------------+
// | PHPMETRICS |
// +------------+
$path_src = explode('/', $src);
if(sizeof($data["METRICS"]) > 0) {
    $html .= "<div id=\"tab-phpmetrics\">\n";
    foreach ($data["METRICS"] as $file => $metrics) {
        $html .= "<h2>".$file."</h2>\n";

        $html .= "<table align=\"center\" width=\"75%\">\n";
        $html .= "    <tr>\n";
        $html .= "        <th></th>\n";

        $metrics = $metrics[ $path_src[0] ];
        if(count($path_src) == 2) $metrics = $metrics[ $path_src[1] ];
        foreach($metrics["#VALUE"] as $col_name => $val) {
            $html .= "        <th width=\"10%\" align=\"center\">".$col_name."</th>\n";
        }
        $html .= "    </tr>\n";
        $data_html = '';
        /*foreach($metrics as $fname => $rowmetrics) {
          if($fname != '#VALUE') {
            $html .= displayRowMetrics($fname, $rowmetrics, $data_html);
          }
        }*/
        displayRowMetrics('.', $metrics, $data_html);
        $html .= $data_html;
        $html .= "</table>\n";
    }
    $html .= "</div>";

    $html .= "<div id=\"tab-phpmetrics-dashboard\">\n";
    $html .= "<iframe src='phpmetrics.html' height='100%' width='100%' style='height: 100%' frameborder=0></iframe>";
    $html .= "</div>";
}

// +-------------+
// |   PHPUNIT   |
// +-------------+
$html .= "<div id=\"tab-phpunit-coverage\">\n";
$html .= "<iframe src='phpunit/unit/coverage/index.html' height='100%' width='100%' frameborder=0></iframe>";
$html .= "<iframe src='phpunit/integration/coverage/index.html' height='100%' width='100%' frameborder=0></iframe>";
$html .= "<iframe src='phpunit/regression/coverage/index.html' height='100%' width='100%' frameborder=0></iframe>";
$html .= "<iframe src='phpunit/specification/coverage/index.html' height='100%' width='100%' frameborder=0></iframe>";
$html .= "</div>";

$html .= "</div>\n";

$html .= "
      </body>
    </html>
   ";

file_put_contents($dir_to_analyze."/dashboard-snapshot-v1.html", $html);
