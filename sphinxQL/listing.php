<?php
/*
������ ��������� ������� ������ � ���������� $count � �� ���������� ������ ������� ������� ���������� �� ���������� $list

��������� � ����������:
$url - ��� ������� �������� (�� �����������)
$count - ����� �������
$folio - ������� ����� ��������
$max - ���-�� ������ ������� ��������� �� ��������

������������� CSS-�������:
<style type="text/css">
#Listing { padding: 5px 15px; text-align:center; }
#Listing SPAN { line-height: 25px; padding: 0 5px; }
#Listing EM { font-weight: bold }
</style>
*/

$list = '';// ������ ������� ���������� ������
if($count){
  if(!$url){ $url = $_SERVER['PHP_SELF'].'?nothing'; }// ����� ���
  if(!$listing){ $listing = 3; }// ���� ��� �������� �� ������, �� ������ ��� ������������
  if($count>$max){
    $last = $separator = $next = '';
    if($listing==1 || $listing==3){// ������ ������� �������
      $n = 0;
      for($i=0; $i<$count; $i+=$max){
        $separator .= '<span class="Page">';
        if($n==$folio){
          $separator .= '<em>'.($n+1).'</em>';
        }else{
          $separator .= '<a href="'.$url.'&folio='.$n.'">'.($n+1).'</a>';
        }
        $separator .= "</span>\n";
        $n++;
      }
      if($n<2){ $separator = ''; }
    }
    if($listing>1){// ������ ���������� | ���������
      $last = $folio?'<a class="inThePresent" href="'.$url.'&folio='.($folio-1).'">&laquo; ����������</a> ':'';
      $next = ($count>( ($folio*$max) + $max) )? ' <a class="inThePast" href="'.$url.'&folio='.($folio+1).'">��������� &raquo;</a>' : '';
    }
    $list = '<div id="Listing">'.$last.$separator.$next.'</div>';
  }
}
?>
