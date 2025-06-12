<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가;

add_event('tail_sub', 'se2_custom');
function se2_custom() {
	global $board, $wr_id, $config, $member; 
	if (basename($_SERVER['PHP_SELF']) === "write.php" && ($wr_id || $wr_id === 0) && $board['bo_use_dhtml_editor'] && preg_match('/^smarteditor/i', $config['cf_editor'])) {
		$upload_display = $member['mb_level'] < $board['bo_upload_level'] ? "none" : "block";
		echo "
			<script>
			document.addEventListener('DOMContentLoaded', () => {
			    se2Width = '100%';
				wr_content.nextSibling.onload = function() {
					se2Custom = this['contentWindow']['document'];
					se2Custom.querySelector('#smart_editor2').style.width = se2Custom.querySelector('#smart_editor2').style.maxWidth = se2Custom.querySelector('#smart_editor2').style.minWidth = se2Width;
					se2Custom.querySelector('#smart_editor2 .se2_text_tool').style.padding = '0px 0px 5px 5px';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type li').style.marginLeft = '-1px';
					for (se2_tt_ul of se2Custom.querySelectorAll('#smart_editor2 .se2_text_tool ul')) se2_tt_ul.style.paddingTop = '5px';
					se2Custom.querySelector('#smart_editor2 .se2_bx_character .se2_s_character ul').style = marginTop = '5px';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_multy').style.float = 'right';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_multy').style.paddingRight = '4px';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_multy').style.position = 'static';			
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_multy').style.height = '21px';					
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_multy').style.border = 'none';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_multy button').style.height = '21px';
					//se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_multy button').style.backgroundColor = '#f4e3d5';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_multy button').style.border = '1px solid #bbbbbb';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_multy .se2_icon').style.marginTop = se2Custom.querySelector('#smart_editor2 .se2_text_tool button span.se2_mntxt').style.marginTop = '-5px';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_multy').style.display = '".$upload_display."';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').style.position = 'relative';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').style.zIndex = '60';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').nextSibling.style.position = 'relative';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').nextSibling.style.zIndex = '59';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').nextSibling.nextSibling.style.position = 'relative';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').nextSibling.nextSibling.style.zIndex = '58';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').nextSibling.nextSibling.nextSibling.style.position = 'relative';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').nextSibling.nextSibling.nextSibling.style.zIndex = '57';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').nextSibling.nextSibling.nextSibling.nextSibling.style.position = 'relative';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').nextSibling.nextSibling.nextSibling.nextSibling.style.zIndex = '56';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.style.position = 'relative';
					se2Custom.querySelector('#smart_editor2 .se2_text_tool .se2_font_type').nextSibling.nextSibling.nextSibling.nextSibling.nextSibling.style.zIndex = '55';
					se2Custom.querySelector('#smart_editor2 .se2_conversion_mode').style.padding = '5px';
					se2Custom.querySelector('#smart_editor2 .se2_conversion_mode').style.backgroundColor = '#f9f9f9';
					se2Custom.querySelector('#smart_editor2 .se2_inputarea_controller').style.textAlign = 'left';
					se2Custom.querySelector('#smart_editor2 .se2_inputarea_controller').style.paddingLeft = '5px';
					se2Custom.querySelector('#smart_editor2 #se2_iframe').onload = function() {
						this['contentWindow']['document'].querySelector('body').insertAdjacentHTML('beforebegin', '<style>img { max-width:100%; }</style>');
					}
				}
			} );
			</script>
		";
	}
}