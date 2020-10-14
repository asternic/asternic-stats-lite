<?php
/*
   Copyright 2007, 2020 Nicolás Gudiño

   This file is part of Asternic Call Center Stats.

    Asternic Call Center Stats is free software: you can redistribute it 
    and/or modify it under the terms of the GNU General Public License as 
    published by the Free Software Foundation, either version 3 of the 
    License, or (at your option) any later version.

    Asternic Call Center Stats is distributed in the hope that it will be 
    useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Asternic Call Center Stats.  If not, see 
    <http://www.gnu.org/licenses/>.
*/

function return_timestamp($date_string)
{
  list ($year,$month,$day,$hour,$min,$sec) = preg_split("/-|:| /",$date_string,6);
  $u_timestamp = mktime($hour,$min,$sec,$month,$day,$year);
  return $u_timestamp;
}

function swf_bar($values,$width,$height,$divid,$stack) {

?>

<canvas id="<?php echo $divid?>" width='<?php echo $width;?>' height='<?php echo $height;?>'></canvas>


<script>

<?php
parse_str($values,$options);

$colores = array('#FF6600','#538353');
$labels  = array();
$dvalues = array();

foreach($options as $key=>$val) {
    if(substr($key,0,3)=="var") {
        $labels[]=$val;
    } else if(substr($key,0,3)=="tag") {
        $series = substr($key,3,1);
        $seriename[$series]=$val;
    } else if(substr($key,0,3)=="val") {
        if($stack==0) {
            $dvalues['A'][]=$val;
        } else {
            $series = substr($key,3,1);
            $dvalues[$series][]=$val;
        }
    }
}

if(!isset($seriename['A'])) {
    if(preg_match("/secs/",$options['title'])) {
        $seriename['A']="Seconds";
    } else {
        $seriename['A']="Count";
    }
}

$labelstext = "'".implode("','",$labels)."'";

?>

var barChartData_<?php echo $divid;?> = {
    labels: [<?php echo $labelstext;?>],
    datasets: [

<?php
foreach($dvalues as $serie=>$points) {
    $valuestext = implode(",",$points);
    $color = array_shift($colores);
?>
{
backgroundColor: '<?php echo $color;?>',
label: '<?php echo $seriename[$serie];?>',
data: [
<?php echo $valuestext;?>
]
},
<?php } ?>

    ]
};

var ctx = document.getElementById('<?php echo $divid;?>').getContext('2d');
var myChart = new Chart(ctx, {
type: 'bar',
	data: barChartData_<?php echo $divid;?>,
	options: {
		title: {
			display: true,
			text: '<?php echo $options['title'];?>'
		},
		tooltips: {
			mode: 'index',
			intersect: false
		},
		responsive: true
	}
});
</script>

<?php
}

function tooltip($texto,$width) {
 echo " onmouseover=\"this.T_WIDTH=$width;this.T_PADDING=5;this.T_STICKY = false; return escape('$texto')\" ";
}


function print_exports($header_pdf,$data_pdf,$width_pdf,$title_pdf,$cover_pdf) {
		global $lang;
		global $language;
		$head_serial = serialize($header_pdf);
		$data_serial = serialize($data_pdf);
		$width_serial = serialize($width_pdf);
		$title_serial = serialize($title_pdf);
		$cover_serial = serialize($cover_pdf);
		$head_serial = rawurlencode($head_serial);
		$data_serial = rawurlencode($data_serial);
		$width_serial = rawurlencode($width_serial);
		$title_serial = rawurlencode($title_serial);
		$cover_serial = rawurlencode($cover_serial);
		echo "<BR><form method=post action='export.php'>\n";
		echo $lang["$language"]['export'];
		echo "<input type='hidden' name='head' value='".$head_serial."' />\n";
		echo "<input type='hidden' name='rawdata' value='".$data_serial."' />\n";
		echo "<input type='hidden' name='width' value='".$width_serial."' />\n";
		echo "<input type='hidden' name='title' value='".$title_serial."' />\n";
		echo "<input type='hidden' name='cover' value='".$cover_serial."' />\n";
		echo "<input type=image name='pdf' alt='export to pdf' src='images/pdf.gif' ";
		tooltip($lang["$language"]['pdfhelp'],200);
		echo ">\n";
		echo "<input type=image name='csv' alt='export to csv' src='images/excel.gif' "; 
		tooltip($lang["$language"]['csvhelp'],200);
		echo ">\n";
		echo "</form>";
}

function seconds2minutes($segundos) {
    $minutos = intval($segundos / 60);
    $segundos = $segundos % 60;
    if(strlen($segundos)==1) {
		$segundos = "0".$segundos;
	}
    return "$minutos:$segundos";
}
?>
