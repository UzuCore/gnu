/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

//  모바일 체크
if(typeof(g5_is_mobile) == "undefined") g5_is_mobile = false;

CKEDITOR.editorConfig = function( config ) {
	// 에디터 높이 설정
	if(typeof(editor_height) != "undefined") {
		config.height = editor_height+"px";
	}

	// 언어 설정
	config.language = 'ko';
	// 글꼴관련
	config.font_names = '맑은 고딕;굴림;굴림체;궁서;궁서체;돋움;돋움체;바탕;바탕체;';  // + CKEDITOR.config.font_names;
	config.font_defaultLabel = '맑은 고딕';
	//config.font_defaultLabel = 'Malgun Gothic';
	// 글자크기 출력
	config.fontSize_sizes = '8pt;9pt;10pt;11pt;12pt;14pt;16pt;20pt;24pt;';

	// 툴바 기능버튼 순서
	config.toolbarGroups = [
		{ name: '1', groups: [ 'styles', 'align', 'basicstyles', 'cleanup' ] },
		{ name: '2', groups: [ 'insertImg', 'insert', 'colors', 'list', 'blocks', 'links', 'mode', 'tools', 'about' ] }
	];
	// 미노출 기능버튼
	if(g5_is_mobile) {
		//--- 모바일 ---//
		config.removeButtons = 'Print,Cut,Copy,Paste,Subscript,Superscript,Anchor,Unlink,ShowBlocks,Undo,Redo,Smiley,Font,Blockquote,Table,Source,RemoveFormat,About';
	} else {
		//--- PC ---//
		config.removeButtons = 'Print,Cut,Copy,Paste,Subscript,Superscript,Anchor,Unlink,ShowBlocks,Undo,Redo,Smiley,Blockquote,Table,Source,RemoveFormat,About';
	}

	/* 이미지 업로드 관련 소스 */
	var up_url = "/upload.php?type=Images";
	if( typeof(g5_editor_url) != "undefined" )	{
		up_url = g5_editor_url + up_url;
	} else {
		up_url = "/plugin/editor/ckeditor4" + up_url;
	}
	// 에디터 구분
	if(typeof(editor_id) != "undefined" && editor_id != "") {
		up_url += "&editor_id="+editor_id;
	}
	// 업로드 경로 - editor_uri
	if(typeof(editor_uri) != "undefined" && editor_uri != "") {
		up_url += "&editor_uri="+editor_uri;
	}
	// 업로드 이미지용 토큰
	if( typeof(editor_form_name) != "undefined" && editor_form_name != "") {
		up_url += "&editor_form_name="+editor_form_name;
	}
    
	// 업로드 페이지 URL 선언
	config.filebrowserImageUploadUrl = up_url;

	// 이미지 다이얼로그 수정 
	CKEDITOR.on('dialogDefinition', function (ev) {
		var dialogName = ev.data.name;
		var dialog = ev.data.definition.dialog;
		var dialogDefinition = ev.data.definition;
		if (dialogName == 'image') {
			dialog.on('show', function (obj) {
				//this.selectPage('Upload'); //업로드텝으로 시작
			});
			dialogDefinition.removeContents('advanced'); // 자세히탭 제거
			dialogDefinition.removeContents('Link'); // 링크탭 제거
			
			var infoTab = dialogDefinition.getContents('info');   
			infoTab.remove('txtHSpace');
			infoTab.remove('txtVSpace');
			infoTab.remove('htmlPreview');	// 미리보기 제거
		}
	});

	// 사용할 플러그인 추가
	config.extraPlugins = 'uploadwidget,uploadimage';

	// 본문내용 불러들일때 속성유지
	config.allowedContent = true;

	// iOS만 적용
	if(/iPhone|iPad|iPod/i.test(navigator.userAgent) ) {
		// 한글 입력 관련 줄바꿈 과정에서 문제발생하여 적용
		config.removePlugins = 'enterkey';
	}

	CKEDITOR.on('instanceReady', function(ev) {
		var editor = ev.editor;
		var MAX_IMAGES = 10; // 원하는 이미지 최대 개수 10개로 설정

		// 에디터가 초기화되면 이미지 삽입 시마다 체크
		editor.on('fileUploadRequest', function(evt) {
			var html = editor.getData();
			var matches = html.match(/<img\b[^>]*>/gi);
			var imgCount = matches ? matches.length : 0;

			// 이미지 개수가 제한을 초과하면 업로드 취소
			if (imgCount >= MAX_IMAGES) {
				alert('이미지는 최대 ' + MAX_IMAGES + '장까지만 등록 가능합니다.');
				evt.cancel(); // 업로드 취소
			}
		});

		// 이미지 삽입 시 자동으로 100% 크기 적용 및 줄바꿈 추가
		editor.on('insertElement', function(evt) {
			var element = evt.data;

			// 이미지 태그일 때만 스타일 추가
			if (element.getName() === 'img') {
				element.setStyle('max-width', '100%');
				element.setStyle('height', 'auto');

				// 이미지 뒤에 <br> 태그 삽입
				var brElement = editor.document.createElement('br');
				editor.insertElement(brElement); // 이미지 삽입 후 줄바꿈 추가
			}
		});
	});

};
