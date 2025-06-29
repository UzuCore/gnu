<?php
// ----------- [검색 환경/옵션] -----------
$max_result = 1000000; // 검색 결과 최대 노출 개수 (속도 개선 목적, AJAX와 일치시켜야 함)
  
$use_external = false; // true 시 외부 DB 사용
$external_db = [       // 외부 DB 연결 정보(사용 시 배열에 host,user,pass,name)
    'host' => '외부서버IP',
    'user' => '외부계정',
    'pass' => '비밀번호',
    'name' => '외부DB명'
];
$exclude_words = ['운영자', '변재일', 'byunjaeil']; // 무조건 검색에서 제외할 단어(관리자/운영자/닉 등)
if ($is_member && $member['mb_adult'])
    $excluded_bo_tables = ['client', 'favorite'];
else
    $excluded_bo_tables = ['client', 'favorite', 'event', 'trade']; // 비회원/비성인에게 추가로 숨길 게시판
$excluded_ids = ['byunjaeil']; // 특정 회원 ID 제외
$excluded_names = ['이베이프']; // 특정 닉네임 제외
