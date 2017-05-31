<?php
	class PaginateComponent {

		var $total = 0; //總筆數
		var $perpage = 10; //每頁筆數
		var $current = 1; //目前所在頁數
		var $totalpage = 0;//總頁數
		var $cursor =0; //資料庫cursor
		var $url = ''; //網址
		var $student = true;
		var $enterprise = false;
		var $elective = false;
		
		function get_cursor($total){
			
			$this->total = $total;
			$this->totalpage = ceil( $total / $this->perpage );			
			$this->current = isset($_GET['cursor']) && is_numeric($_GET['cursor']) 
								&& $_GET['cursor'] <= $this->totalpage && $_GET['cursor']>0 ?
								$_GET['cursor'] : 1;
			$this->cursor = ( $this->current - 1 ) * $this->perpage;			
			return $this->cursor;
		}
		
		function pager(){
			
			if(!$this->total) return '<nav class="pagination pagination-student">&nbsp;</nav>';
			
			$url = preg_match("/\?/i", $this->url ) ? $this->url.'&' : $this->url.'?' ;

			if($this->enterprise)
				 $css = 'enterprise';
			elseif($this->elective)
				 $css = 'elective';
			else $css = $this->student ? 'student' : 'teacher';
			
			$pager ='<nav class="pagination pagination-'.$css.'"><ul>';
			
			if($this->current!=1)
				$pager.="<a class='prev' href='{$url}cursor=".($this->current-1)."'> <span>Previous</span></a>";
			//else $pager.='<a class="prev"><span>Previous</span></a>';
			
			$end = $this->current % 10 == 0 ? $this->current : ( $this->current + 10 ) - ( $this->current % 10 );
			$start = $end - 9;
			$end =  $end > $this->totalpage ? $this->totalpage : $end;
			
			for($i=$start ; $i<= $end; $i++){
				if($this->current == $i) $pager.="<li><a href='{$url}cursor={$i}' class='active'>{$i}</a></li>";
				else $pager.="<li><a href='{$url}cursor={$i}'>{$i}</a></li>";
			}
			
			if($this->current<$this->totalpage )
				$pager.= "<a class='next' href='{$url}cursor=".($this->current+1)."'> <span>Next</span></a>";
			//else $pager.= "<a class='next'> <span>Next</span></a>";

			$pager.='</ul></nav>';	
			return $pager;
		}
	}
?>