<?php
// ===== 설정 =====
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once("../../../../../common.php");

// 관리자 권한 확인
if (!$is_admin) { 
    alert('관리자만 접근 가능합니다.');
    exit;
}

// API 설정
//define('DEEPL_API_KEY', 'dffe91f8-e220-4ab8-bff3-0c0815071818:fx');
define('DEEPL_API_KEY', '');
define('GOOGLE_CLOUD_API_KEY', 'AIzaSyAM8cl_TnfCb_BxOVzhMH6bt9Nimc6x1q4'); // 여기에 Google Cloud API 키 입력 (공란이면 사용 안함)
define('SCREENSCRAPER_DEVID', 'jelos');
define('SCREENSCRAPER_DEVPASSWORD', 'jelos');
define('SCREENSCRAPER_SOFTNAME', 'EmulatorJS');

// 시스템 매핑
$systemMap = [
    // Nintendo 계열
    'nes' => 3,
    'snes' => 4,
    'n64' => 14,
    'gb' => 9,
    'gba' => 12,
    'nds' => 15,
    'vb' => 11,
    
    // Sega 계열
    'segaMS' => 2,
    'segaMD' => 1,
    'segaGG' => 8,
    'segaCD' => 20,
    'sega32x' => 19,
    'segaSaturn' => 22,
    
    // Sony 계열
    'psx' => 57,
    'psp' => 61,
    
    // Atari 계열
    'atari2600' => 26,
    'atari5200' => 40,
    'atari7800' => 41,
    'lynx' => 28,
    'jaguar' => 27,
    
    // 아케이드 및 기타
    'arcade' => 75,
    '3do' => 29,
    'pce' => 31,
    'ngp' => 25,
    'ws' => 45,
    'coleco' => 48,
    'msx' => 113,
    'c64' => 66,
    'amiga' => 64,
    'fbneo' => 75,
    'fbalpha2012_cps1' => 75,
    'fbalpha2012_cps2' => 75,
    'mame2003_plus' => 75,
    'dos' => 135
];

// ===== 유틸리티 함수 =====
function extractCountryFromFilename($fileName) {
    // 파일명에서 국가 정보 추출 및 한글 변환
    $countryMap = [
        'korea' => '한국',
        'korean' => '한국',
        'kr' => '한국',
        'japan' => '일본',
        'japanese' => '일본',
        'jp' => '일본',
        'usa' => '미국',
        'us' => '미국',
        'europe' => '유럽',
        'eu' => '유럽',
        'world' => '해외',
        'international' => '해외',
        'global' => '해외',
        'asia' => '아시아',
        'china' => '중국',
        'chinese' => '중국',
        'cn' => '중국',
        'taiwan' => '대만',
        'tw' => '대만',
        'hongkong' => '홍콩',
        'hk' => '홍콩',
        'brazil' => '브라질',
        'france' => '프랑스',
        'fr' => '프랑스',
        'germany' => '독일',
        'de' => '독일',
        'spain' => '스페인',
        'es' => '스페인',
        'italy' => '이탈리아',
        'it' => '이탈리아',
        'australia' => '호주',
        'au' => '호주',
        'canada' => '캐나다',
        'ca' => '캐나다',
        'mexico' => '멕시코',
        'russia' => '러시아',
        'ru' => '러시아',
        'sweden' => '스웨덴',
        'se' => '스웨덴',
        'norway' => '노르웨이',
        'no' => '노르웨이',
        'denmark' => '덴마크',
        'dk' => '덴마크',
        'finland' => '핀란드',
        'fi' => '핀란드',
        'netherlands' => '네덜란드',
        'nl' => '네덜란드',
        'uk' => '영국',
        'england' => '영국'
    ];
    
    // 괄호 안의 내용들을 모두 찾기
    preg_match_all('/\(([^)]+)\)/', $fileName, $matches);
    
    $countries = [];
    if (!empty($matches[1])) {
        foreach ($matches[1] as $content) {
            $content = strtolower(trim($content));
            
            // 쉼표로 분리된 여러 국가 처리
            $parts = array_map('trim', explode(',', $content));
            
            foreach ($parts as $part) {
                if (isset($countryMap[$part])) {
                    $countries[] = $countryMap[$part];
                } else {
                    // 매핑에 없는 경우 기본적으로 해외로 처리
                    // 단, 숫자나 특수 문자만 있는 경우는 제외
                    if (preg_match('/^[a-z]+$/', $part) && strlen($part) > 1) {
                        $countries[] = '해외';
                    }
                }
            }
        }
    }
    
    // 중복 제거 후 반환
    return array_unique($countries);
}

function addCountryToTitle($title, $fileName) {
    $countries = extractCountryFromFilename($fileName);
    
    if (!empty($countries)) {
        // 여러 국가가 있는 경우 쉼표로 연결
        $countryString = '(' . implode(', ', $countries) . ')';
        return $title . ' ' . $countryString;
    }
    
    return $title;
}

function addLineBreaks($text, $maxLength = 75) {
    if (empty($text)) return '';
    
    // 문장 단위로 분리
    $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $result = '';
    
    foreach ($sentences as $sentence) {
        $sentence = trim($sentence);
        if (empty($sentence)) continue;
        
        // 문장이 적당한 길이면 그대로 사용
        if (mb_strlen($sentence, 'UTF-8') <= $maxLength) {
            $result .= $sentence;
            if (preg_match('/[.!?]$/', $sentence)) {
                $result .= "\n";
            } else {
                $result .= " ";
            }
            continue;
        }
        
        // 긴 문장 처리
        $words = preg_split('/\s+/', $sentence, -1, PREG_SPLIT_NO_EMPTY);
        $currentLine = '';
        
        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            $potentialLine = $currentLine . ($currentLine ? ' ' : '') . $word;
            
            // 남은 단어들의 길이 계산
            $remainingWords = array_slice($words, $i + 1);
            $remainingLength = mb_strlen(implode(' ', $remainingWords), 'UTF-8');
            
            // 현재 줄이 너무 길거나, 남은 단어가 너무 짧을 때 줄바꿈
            if (mb_strlen($potentialLine, 'UTF-8') > $maxLength && !empty($currentLine)) {
                $result .= $currentLine . "\n";
                $currentLine = $word;
            } 
            // 남은 글자가 15자 이하면 미리 줄바꿈 (더 여유있게)
            else if (mb_strlen($potentialLine, 'UTF-8') > ($maxLength - 20) && 
                     $remainingLength > 0 && $remainingLength <= 15 && !empty($currentLine)) {
                $result .= $currentLine . "\n";
                $currentLine = $word;
            }
            // 쉼표가 있고 적당한 길이일 때만 줄바꿈 고려
            else if (preg_match('/,$/', $word) && 
                     mb_strlen($potentialLine, 'UTF-8') >= 35 && 
                     mb_strlen($potentialLine, 'UTF-8') <= $maxLength &&
                     $remainingLength > 20) {
                $currentLine = $potentialLine;
                $result .= $currentLine . "\n";
                $currentLine = '';
            } else {
                $currentLine = $potentialLine;
            }
        }
        
        if (!empty($currentLine)) {
            $result .= $currentLine . "\n";
        }
    }
    
    // 정리
    $result = preg_replace('/\n{3,}/', "\n\n", $result);
    
    return trim($result);
}

// ===== ScreenScraper API 함수 =====
function fetchGameInfo($fileName, $system) {
    global $systemMap;
    $systemId = $systemMap[$system] ?? 1;
    $gameName = preg_replace('/[\[\(].*?[\]\)]/', '', pathinfo($fileName, PATHINFO_FILENAME));
    $gameName = trim(preg_replace('/\s+/', ' ', preg_replace('/[^\w\s]/', ' ', $gameName)));
    
    $params = http_build_query([
        'devid' => SCREENSCRAPER_DEVID,
        'devpassword' => SCREENSCRAPER_DEVPASSWORD,
        'softname' => SCREENSCRAPER_SOFTNAME,
        'output' => 'json',
        'systemeid' => $systemId,
        'sspassword' => '',
        'recherche' => $gameName,
        'romtype' => 'rom',
        'romnom' => $fileName
    ]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://www.screenscraper.fr/api2/jeuInfos.php?" . $params,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => SCREENSCRAPER_SOFTNAME . '/1.0',
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response) return null;
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['response']['jeu'])) return null;
    
    $jeu = $data['response']['jeu'];
    
    if (is_array($jeu) && count($jeu) > 1) {
        $first = reset($jeu);
        if (isset($first['id'])) return fetchGameDetail($first['id']);
    }
    
    return $jeu;
}

function fetchGameDetail($gameId) {
    $params = http_build_query([
        'devid' => SCREENSCRAPER_DEVID,
        'devpassword' => SCREENSCRAPER_DEVPASSWORD,
        'softname' => SCREENSCRAPER_SOFTNAME,
        'output' => 'json',
        'sspassword' => '',
        'gameid' => $gameId
    ]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://www.screenscraper.fr/api2/jeuInfos.php?" . $params,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => SCREENSCRAPER_SOFTNAME . '/1.0',
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if (!$response) return null;
    $data = json_decode($response, true);
    return $data['response']['jeu'] ?? null;
}

// ===== 번역 함수 =====
function translateWithDeepL($text, $isTitle = false) {
    if (empty($text) || empty(DEEPL_API_KEY) || DEEPL_API_KEY === 'YOUR_DEEPL_API_KEY') {
        return '';
    }
    
    $data = http_build_query([
        'auth_key' => DEEPL_API_KEY,
        'text' => $text,
        'source_lang' => 'EN',
        'target_lang' => 'KO'
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $data
        ]
    ]);
    
    $response = @file_get_contents('https://api-free.deepl.com/v2/translate', false, $context);
    
    if ($response) {
        $result = json_decode($response, true);
        $translated = $result['translations'][0]['text'] ?? '';
        // 제목은 줄바꿈 처리 안함
        return $isTitle ? $translated : addLineBreaks($translated);
    }
    
    return '';
}

function translateWithGoogleCloud($text, $isTitle = false) {
    if (empty($text) || empty(GOOGLE_CLOUD_API_KEY) || GOOGLE_CLOUD_API_KEY === 'YOUR_GOOGLE_CLOUD_API_KEY') {
        return '';
    }
    
    $maxLength = 5000; // Google Cloud API 텍스트 길이 제한
    
    // 긴 텍스트는 문장 단위로 분할하여 번역
    if (strlen($text) > $maxLength) {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $translatedSentences = [];
        $currentBatch = '';
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            
            if (strlen($currentBatch . ' ' . $sentence) > $maxLength && !empty($currentBatch)) {
                // 현재 배치를 번역
                $translated = translateSingleTextWithGoogleCloud($currentBatch);
                if ($translated) {
                    $translatedSentences[] = $translated;
                }
                $currentBatch = $sentence;
                
                // API 호출 제한을 위한 딜레이
                usleep(100000); // 0.1초
            } else {
                $currentBatch .= ($currentBatch ? ' ' : '') . $sentence;
            }
        }
        
        // 마지막 배치 번역
        if (!empty($currentBatch)) {
            $translated = translateSingleTextWithGoogleCloud($currentBatch);
            if ($translated) {
                $translatedSentences[] = $translated;
            }
        }
        
        $result = implode(' ', $translatedSentences);
        return $isTitle ? $result : addLineBreaks($result);
    } else {
        $translated = translateSingleTextWithGoogleCloud($text);
        return $isTitle ? $translated : addLineBreaks($translated);
    }
}

function translateSingleTextWithGoogleCloud($text) {
    if (empty($text) || empty(GOOGLE_CLOUD_API_KEY) || GOOGLE_CLOUD_API_KEY === 'YOUR_GOOGLE_CLOUD_API_KEY') {
        return '';
    }
    
    $url = 'https://translation.googleapis.com/language/translate/v2?key=' . GOOGLE_CLOUD_API_KEY;
    
    $data = json_encode([
        'q' => $text,
        'source' => 'en',
        'target' => 'ko',
        'format' => 'text'
    ]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$response || $error) {
        error_log("Google Cloud Translation API Error: HTTP $httpCode, Error: $error, Response: $response");
        return '';
    }
    
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        return '';
    }
    
    return $result['data']['translations'][0]['translatedText'] ?? '';
}

function translateWithGoogle($text, $isTitle = false) {
    if (empty($text)) return '';
    
    $maxLength = 500;
    
    if (strlen($text) <= $maxLength) {
        $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=ko&dt=t&q=" . urlencode($text);
        $response = @file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            $translatedText = $data[0][0][0] ?? $text;
            // 제목은 줄바꿈 처리 안함
            return $isTitle ? $translatedText : addLineBreaks($translatedText);
        }
    } else {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $translatedSentences = [];
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($sentence) > 0) {
                $url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=ko&dt=t&q=" . urlencode($sentence);
                $response = @file_get_contents($url);
                
                if ($response !== false) {
                    $data = json_decode($response, true);
                    $translated = $data[0][0][0] ?? $sentence;
                    $translatedSentences[] = $translated;
                } else {
                    $translatedSentences[] = $sentence;
                }
                
                usleep(200000);
            }
        }
        
        $result = implode(' ', $translatedSentences);
        return $isTitle ? $result : addLineBreaks($result);
    }
    
    return $text;
}

// ===== 그누보드 게시물 업데이트 함수 =====
function updateBoardContent($bo_table, $wr_id, $newContent, $newTitle = null) {
    // 기존 게시물 내용 가져오기
    $sql = "SELECT wr_content FROM " . $GLOBALS['g5']['write_prefix'] . $bo_table . " WHERE wr_id = '$wr_id'";
    $result = sql_fetch($sql);
    
    if (!$result) {
        return false;
    }
    
    $existingContent = $result['wr_content'];
    
    // SCREENSHOT 부분 찾기
    $screenshotPattern = '/<!-- SCREENSHOT_START -->.*?<!-- SCREENSHOT_END -->/s';
    $screenshotMatch = '';
    
    if (preg_match($screenshotPattern, $existingContent, $matches)) {
        $screenshotMatch = $matches[0];
    }
    
    // 새로운 내용 구성 (스크린샷 + 번역 내용)
    // 이스케이프된 문자 복원 후 줄바꿈을 <br> 태그로 변환
    $cleanContent = stripslashes($newContent);
    $formattedContent = nl2br($cleanContent);
    
    $finalContent = '';
    if (!empty($screenshotMatch)) {
        $finalContent = $screenshotMatch . "\n\n" . $formattedContent;
    } else {
        $finalContent = $formattedContent;
    }
    
    // 게시물 업데이트 쿼리 준비
    $escapedContent = sql_real_escape_string($finalContent);
    $escapedBoTable = sql_real_escape_string($bo_table);
    $escapedWrId = sql_real_escape_string($wr_id);
    
    // 트랜잭션 시작
    sql_query("BEGIN");
    
    try {
        if ($newTitle !== null) {
            // 제목도 함께 업데이트
            $escapedTitle = sql_real_escape_string($newTitle);
            
            // SEO 제목 생성 (그누보드 함수 사용)
            $write_table = $GLOBALS['g5']['write_prefix'] . $bo_table;
            $wr_seo_title = exist_seo_title_recursive('bbs', generate_seo_title($newTitle), $write_table, $wr_id);
            $escapedSeoTitle = sql_real_escape_string($wr_seo_title);
            
            // 1. 메인 게시물 테이블 업데이트
            $sql1 = "UPDATE " . $GLOBALS['g5']['write_prefix'] . $bo_table . " 
                     SET wr_content = '{$escapedContent}', 
                         wr_subject = '{$escapedTitle}',
                         wr_seo_title = '{$escapedSeoTitle}'
                     WHERE wr_id = '{$escapedWrId}'";
            
            // 2. emu_posts 테이블 업데이트 (제목 + 내용)
            $sql2 = "UPDATE emu_posts 
                     SET wr_subject = '{$escapedTitle}',
                         wr_content = '{$escapedContent}'
                     WHERE bo_table = '{$escapedBoTable}' AND wr_id = '{$escapedWrId}'";
            
        } else {
            // 내용만 업데이트
            
            // 1. 메인 게시물 테이블 업데이트
            $sql1 = "UPDATE " . $GLOBALS['g5']['write_prefix'] . $bo_table . " 
                     SET wr_content = '{$escapedContent}' 
                     WHERE wr_id = '{$escapedWrId}'";
            
            // 2. emu_posts 테이블 업데이트 (내용만)
            $sql2 = "UPDATE emu_posts 
                     SET wr_content = '{$escapedContent}'
                     WHERE bo_table = '{$escapedBoTable}' AND wr_id = '{$escapedWrId}'";
        }
        
        // 쿼리 실행
        $result1 = sql_query($sql1);
        $result2 = sql_query($sql2);
        
        if ($result1 && $result2) {
            sql_query("COMMIT");
            return true;
        } else {
            sql_query("ROLLBACK");
            return false;
        }
        
    } catch (Exception $e) {
        sql_query("ROLLBACK");
        return false;
    }
}

// ===== 메인 로직 =====
$fileName = urldecode($_GET['game'] ?? '');
$system = $_GET['system'] ?? '';
$bo_table = $_GET['bo_table'] ?? '';
$wr_id = $_GET['wr_id'] ?? '';

if (!$fileName || !$system || !$bo_table || !$wr_id) {
    echo "<script>
        alert('필수 파라미터가 없습니다.');
        history.back();
    </script>";
    exit;
}

// 게시물 업데이트 처리
if (isset($_POST['save'])) {
    $updateTitle = !empty($_POST['title_korean']) ? $_POST['title_korean'] : null;
    
    // 선택된 번역 엔진에 따라 내용 선택
    $selectedEngine = $_POST['selected_engine'] ?? 'deepl';
    $updateContent = '';
    
    switch ($selectedEngine) {
        case 'deepl':
            $updateContent = $_POST['deepl_content'] ?? '';
            break;
        case 'google_cloud':
            $updateContent = $_POST['google_cloud_content'] ?? '';
            break;
        case 'google_free':
            $updateContent = $_POST['google_content'] ?? '';
            break;
    }
    
    if (!empty($updateContent)) {
        $updateResult = updateBoardContent($bo_table, $wr_id, $updateContent, $updateTitle);
        
        if ($updateResult) {
            echo "<script>
                alert('게시물이 성공적으로 업데이트되었습니다.');
                location.href = '" . G5_BBS_URL . "/board.php?bo_table={$bo_table}&wr_id={$wr_id}';
            </script>";
            exit;
        } else {
            $message = '게시물 업데이트에 실패했습니다.';
            $saved = false;
        }
    } else {
        $message = '번역된 내용이 없습니다.';
        $saved = false;
    }
}

// 게임 정보 가져오기
$gameInfo = fetchGameInfo($fileName, $system);
if (!$gameInfo) {
    echo "<script>
        alert('게임을 찾을 수 없습니다: " . addslashes($fileName) . "');
        history.back();
    </script>";
    exit;
}

// 게임 제목 추출
$gameTitle = '';
if (isset($gameInfo['noms']) && is_array($gameInfo['noms'])) {
    foreach ($gameInfo['noms'] as $name) {
        if (isset($name['text'])) {
            $gameTitle = $name['text'];
            break;
        }
    }
}

// 게임 설명 추출
$synopsis = '';
if (isset($gameInfo['synopsis']) && is_array($gameInfo['synopsis'])) {
    foreach ($gameInfo['synopsis'] as $syn) {
        if (isset($syn['langue']) && $syn['langue'] === 'en' && isset($syn['text'])) {
            $synopsis = $syn['text'];
            break;
        }
    }
    if (empty($synopsis)) {
        foreach ($gameInfo['synopsis'] as $syn) {
            if (isset($syn['text']) && !empty($syn['text'])) {
                $synopsis = $syn['text'];
                break;
            }
        }
    }
}

// 번역 실행 - 제목
$titleDeeplTranslation = '';
$titleGoogleCloudTranslation = '';
$titleGoogleTranslation = '';

if (!empty($gameTitle)) {
    // DeepL 번역
    if (!empty(DEEPL_API_KEY) && DEEPL_API_KEY !== 'YOUR_DEEPL_API_KEY') {
        $titleDeeplTranslation = translateWithDeepL($gameTitle, true);
        if (!empty($titleDeeplTranslation)) {
            $titleDeeplTranslation = addCountryToTitle($titleDeeplTranslation, $fileName);
        }
    }
    
    // Google Cloud 번역
    if (!empty(GOOGLE_CLOUD_API_KEY) && GOOGLE_CLOUD_API_KEY !== 'YOUR_GOOGLE_CLOUD_API_KEY') {
        $titleGoogleCloudTranslation = translateWithGoogleCloud($gameTitle, true);
        if (!empty($titleGoogleCloudTranslation)) {
            $titleGoogleCloudTranslation = addCountryToTitle($titleGoogleCloudTranslation, $fileName);
        }
    }
    
    // Google 무료 번역 (항상 사용 가능)
    $titleGoogleTranslation = translateWithGoogle($gameTitle, true);
    if (!empty($titleGoogleTranslation)) {
        $titleGoogleTranslation = addCountryToTitle($titleGoogleTranslation, $fileName);
    }
}

// 번역 실행 - 설명
$deeplTranslation = '';
$googleCloudTranslation = '';
$googleTranslation = '';

if (!empty(DEEPL_API_KEY) && DEEPL_API_KEY !== 'YOUR_DEEPL_API_KEY') {
    $deeplTranslation = translateWithDeepL($synopsis);
}

if (!empty(GOOGLE_CLOUD_API_KEY) && GOOGLE_CLOUD_API_KEY !== 'YOUR_GOOGLE_CLOUD_API_KEY') {
    $googleCloudTranslation = translateWithGoogleCloud($synopsis);
}

$googleTranslation = translateWithGoogle($synopsis);

$gameData = [
    'title' => $gameTitle ?: $fileName,
    'title_deepl' => $titleDeeplTranslation,
    'title_google_cloud' => $titleGoogleCloudTranslation,
    'title_google' => $titleGoogleTranslation,
    'system' => $gameInfo['systeme']['text'] ?? $system,
    'description_en' => $synopsis,
    'description_deepl' => $deeplTranslation,
    'description_google_cloud' => $googleCloudTranslation,
    'description_google' => $googleTranslation,
    'filename' => $fileName
];

// Google Cloud API 키 상태 확인
$deeplEnabled = (!empty(DEEPL_API_KEY) && DEEPL_API_KEY !== 'YOUR_DEEPL_API_KEY');
$googleCloudEnabled = (!empty(GOOGLE_CLOUD_API_KEY) && GOOGLE_CLOUD_API_KEY !== 'YOUR_GOOGLE_CLOUD_API_KEY');
?>

<!DOCTYPE html>
<html>
<head>
    <title>게임 정보 번역 - <?= htmlspecialchars($gameData['title']) ?></title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; }
        .game-info { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .title-section { background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745; }
        .desc { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .deepl { background: #e3f2fd; border-left: 4px solid #2196f3; }
        .google-cloud { background: #f3e5f5; border-left: 4px solid #9c27b0; }
        .google { background: #fff3e0; border-left: 4px solid #ff9800; }
        .api-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 4px; margin: 10px 0; }
        h4 { margin: 0 0 10px 0; }
        .desc-text { line-height: 1.6; margin: 0; white-space: pre-line; }
        .title-text { font-size: 18px; font-weight: bold; margin: 5px 0; }
        .title-original { color: #666; font-size: 16px; }
        .title-translated { color: #333; font-size: 16px; }
        button { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-save { background: #4caf50; color: white; font-size: 16px; }
        .btn-back { background: #6c757d; color: white; }
        .btn-save:hover { background: #45a049; }
        .btn-back:hover { background: #5a6268; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .radio-group { margin: 10px 0; }
        .radio-group label { display: block; margin: 5px 0; cursor: pointer; }
        .radio-group input[type="radio"] { margin-right: 8px; }
        .selected-title { background: #d4edda; padding: 8px; border-radius: 4px; margin: 5px 0; }
        .translation-selector { background: #e9ecef; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .translation-option { margin: 10px 0; padding: 10px; border: 2px solid #dee2e6; border-radius: 6px; cursor: pointer; transition: all 0.3s; }
        .translation-option:hover { border-color: #007bff; background: #f8f9fa; }
        .translation-option.selected { border-color: #007bff; background: #e7f3ff; }
        .translation-option input[type="radio"] { margin-right: 10px; }
        .translation-label { font-weight: bold; color: #495057; }
        .quality-indicator { 
            display: inline-block; 
            padding: 2px 8px; 
            border-radius: 12px; 
            font-size: 12px; 
            margin-left: 8px; 
        }
        .quality-premium { background: #d4edda; color: #155724; }
        .quality-good { background: #cce5ff; color: #004085; }
        .quality-basic { background: #fff3cd; color: #856404; }
    </style>
    <script>
        function selectTranslation(engine) {
            // 모든 옵션에서 selected 클래스 제거
            document.querySelectorAll('.translation-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // 선택된 옵션에 selected 클래스 추가
            document.querySelector(`.translation-option[data-engine="${engine}"]`).classList.add('selected');
            
            // 라디오 버튼 선택
            document.querySelector(`input[name="selected_engine"][value="${engine}"]`).checked = true;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // 기본 선택 (DeepL)
            selectTranslation('deepl');
            
            // 클릭 이벤트 추가
            document.querySelectorAll('.translation-option').forEach(option => {
                option.addEventListener('click', function() {
                    const engine = this.getAttribute('data-engine');
                    selectTranslation(engine);
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h2><?= htmlspecialchars($gameData['title']) ?> - 번역</h2>
        
        <div class="game-info">
            <p><strong>시스템:</strong> <?= htmlspecialchars($gameData['system']) ?></p>
            <p><strong>파일:</strong> <?= htmlspecialchars($gameData['filename']) ?></p>
            <p><strong>게시판:</strong> <?= htmlspecialchars($bo_table) ?> (ID: <?= htmlspecialchars($wr_id) ?>)</p>
        </div>
        
        <?php if (!$deeplEnabled && !$googleCloudEnabled): ?>
            <div class="api-warning">
                ⚠️ <strong>번역 API가 설정되지 않았습니다.</strong><br>
                • DeepL API 키를 설정하면 고품질 번역을 사용할 수 있습니다.<br>
                • Google Cloud Translation API 키를 설정하면 추가 번역 옵션을 사용할 수 있습니다.<br>
                현재는 Google 무료 번역만 사용 가능합니다.
            </div>
        <?php elseif (!$deeplEnabled): ?>
            <div class="api-warning">
                ⚠️ <strong>DeepL API가 설정되지 않았습니다.</strong><br>
                DeepL API 키를 설정하면 더 높은 품질의 번역을 사용할 수 있습니다.
            </div>
        <?php elseif (!$googleCloudEnabled): ?>
            <div class="api-warning">
                ⚠️ <strong>Google Cloud Translation API가 설정되지 않았습니다.</strong><br>
                Google Cloud 번역을 사용하려면 소스 파일에서 GOOGLE_CLOUD_API_KEY를 설정해주세요.
            </div>
        <?php endif; ?>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <!-- 제목 섹션 -->
        <div class="title-section">
            <h3>🎮 게임 제목</h3>
            
            <?php if (!empty($gameData['title'])): ?>
                <div class="title-original">
                    <strong>원제:</strong> <?= htmlspecialchars($gameData['title']) ?>
                    <?php 
                    $originalCountries = extractCountryFromFilename($fileName);
                    if (!empty($originalCountries)): 
                    ?>
                        <span style="color: #666; font-size: 14px;">
                            → 파일에서 감지된 국가: (<?= implode(', ', $originalCountries) ?>)
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($gameData['title_deepl']) && $deeplEnabled): ?>
                    <div class="title-translated">
                        <strong>DeepL 번역:</strong> <?= htmlspecialchars($gameData['title_deepl']) ?>
                        <span class="quality-indicator quality-premium">프리미엄</span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($gameData['title_google_cloud']) && $googleCloudEnabled): ?>
                    <div class="title-translated">
                        <strong>Google Cloud 번역:</strong> <?= htmlspecialchars($gameData['title_google_cloud']) ?>
                        <span class="quality-indicator quality-good">고품질</span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($gameData['title_google'])): ?>
                    <div class="title-translated">
                        <strong>Google 무료 번역:</strong> <?= htmlspecialchars($gameData['title_google']) ?>
                        <span class="quality-indicator quality-basic">기본</span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <h3>📝 게임 설명</h3>
        
        <?php if (!empty($gameData['description_en'])): ?>
            <div class="desc">
                <h4>📄 원문 (English)</h4>
                <div class="desc-text"><?= htmlspecialchars($gameData['description_en']) ?></div>
            </div>
            
            <?php if (!empty($gameData['description_deepl']) && $deeplEnabled): ?>
                <div class="desc deepl">
                    <h4>🤖 DeepL 번역 <span class="quality-indicator quality-premium">프리미엄</span></h4>
                    <div class="desc-text"><?= htmlspecialchars($gameData['description_deepl']) ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($gameData['description_google_cloud']) && $googleCloudEnabled): ?>
                <div class="desc google-cloud">
                    <h4>☁️ Google Cloud 번역 <span class="quality-indicator quality-good">고품질</span></h4>
                    <div class="desc-text"><?= htmlspecialchars($gameData['description_google_cloud']) ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($gameData['description_google'])): ?>
                <div class="desc google">
                    <h4>🌐 Google 무료 번역 <span class="quality-indicator quality-basic">기본</span></h4>
                    <div class="desc-text"><?= htmlspecialchars($gameData['description_google']) ?></div>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <h4>적용할 제목 선택:</h4>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="title_korean" value="" checked>
                        원제 유지: <?= htmlspecialchars($gameData['title']) ?>
                    </label>
                    <?php if (!empty($gameData['title_deepl']) && $deeplEnabled): ?>
                        <label>
                            <input type="radio" name="title_korean" value="<?= htmlspecialchars($gameData['title_deepl']) ?>">
                            DeepL 번역 사용: <?= htmlspecialchars($gameData['title_deepl']) ?>
                        </label>
                    <?php endif; ?>
                    <?php if (!empty($gameData['title_google_cloud']) && $googleCloudEnabled): ?>
                        <label>
                            <input type="radio" name="title_korean" value="<?= htmlspecialchars($gameData['title_google_cloud']) ?>">
                            Google Cloud 번역 사용: <?= htmlspecialchars($gameData['title_google_cloud']) ?>
                        </label>
                    <?php endif; ?>
                    <?php if (!empty($gameData['title_google'])): ?>
                        <label>
                            <input type="radio" name="title_korean" value="<?= htmlspecialchars($gameData['title_google']) ?>">
                            Google 무료 번역 사용: <?= htmlspecialchars($gameData['title_google']) ?>
                        </label>
                    <?php endif; ?>
                </div>
                
                <div class="translation-selector">
                    <h4>💬 사용할 번역 내용 선택:</h4>
                    
                    <?php if (!empty($gameData['description_deepl']) && $deeplEnabled): ?>
                        <div class="translation-option" data-engine="deepl">
                            <input type="radio" name="selected_engine" value="deepl" id="engine_deepl">
                            <label for="engine_deepl" class="translation-label">
                                🤖 DeepL 번역 사용 <span class="quality-indicator quality-premium">프리미엄</span>
                            </label>
                            <div style="margin-top: 8px; color: #666; font-size: 14px;">
                                가장 자연스럽고 정확한 번역 품질을 제공합니다.
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($gameData['description_google_cloud']) && $googleCloudEnabled): ?>
                        <div class="translation-option" data-engine="google_cloud">
                            <input type="radio" name="selected_engine" value="google_cloud" id="engine_google_cloud">
                            <label for="engine_google_cloud" class="translation-label">
                                ☁️ Google Cloud 번역 사용 <span class="quality-indicator quality-good">고품질</span>
                            </label>
                            <div style="margin-top: 8px; color: #666; font-size: 14px;">
                                Google의 최신 AI 번역 기술을 사용합니다.
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($gameData['description_google'])): ?>
                        <div class="translation-option" data-engine="google_free">
                            <input type="radio" name="selected_engine" value="google_free" id="engine_google_free">
                            <label for="engine_google_free" class="translation-label">
                                🌐 Google 무료 번역 사용 <span class="quality-indicator quality-basic">기본</span>
                            </label>
                            <div style="margin-top: 8px; color: #666; font-size: 14px;">
                                무료로 사용 가능하지만 제한적인 번역 품질입니다.
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($gameData['description_deepl']) && empty($gameData['description_google_cloud']) && empty($gameData['description_google'])): ?>
                        <div style="text-align: center; color: #6c757d; padding: 20px;">
                            사용 가능한 번역이 없습니다.<br>
                            API 키를 확인하거나 네트워크 연결을 확인해주세요.
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- 숨겨진 필드들 -->
                <input type="hidden" name="deepl_content" value="<?= htmlspecialchars($gameData['description_deepl']) ?>">
                <input type="hidden" name="google_cloud_content" value="<?= htmlspecialchars($gameData['description_google_cloud']) ?>">
                <input type="hidden" name="google_content" value="<?= htmlspecialchars($gameData['description_google']) ?>">
                
                <button type="submit" name="save" value="1" class="btn-save">💾 게시물에 번역 내용 저장</button>
                <button type="button" onclick="history.back()" class="btn-back">← 뒤로</button>
            </form>
            
        <?php else: ?>
            <div class="desc">
                <p style="color: #6c757d; text-align: center;">게임 설명이 없습니다.</p>
            </div>
            
            <?php if (!empty($gameData['title_deepl']) || !empty($gameData['title_google_cloud']) || !empty($gameData['title_google'])): ?>
                <form method="POST">
                    <h4>적용할 제목 선택:</h4>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="title_korean" value="" checked>
                            원제 유지: <?= htmlspecialchars($gameData['title']) ?>
                        </label>
                        <?php if (!empty($gameData['title_deepl'])): ?>
                            <label>
                                <input type="radio" name="title_korean" value="<?= htmlspecialchars($gameData['title_deepl']) ?>">
                                DeepL 번역 사용: <?= htmlspecialchars($gameData['title_deepl']) ?>
                            </label>
                        <?php endif; ?>
                        <?php if (!empty($gameData['title_google_cloud']) && $googleCloudEnabled): ?>
                            <label>
                                <input type="radio" name="title_korean" value="<?= htmlspecialchars($gameData['title_google_cloud']) ?>">
                                Google Cloud 번역 사용: <?= htmlspecialchars($gameData['title_google_cloud']) ?>
                            </label>
                        <?php endif; ?>
                        <?php if (!empty($gameData['title_google'])): ?>
                            <label>
                                <input type="radio" name="title_korean" value="<?= htmlspecialchars($gameData['title_google']) ?>">
                                Google 무료 번역 사용: <?= htmlspecialchars($gameData['title_google']) ?>
                            </label>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="deepl_content" value="">
                    <input type="hidden" name="google_cloud_content" value="">
                    <input type="hidden" name="google_content" value="">
                    <input type="hidden" name="selected_engine" value="deepl">
                    
                    <button type="submit" name="save" value="1" class="btn-save">💾 제목만 업데이트</button>
                    <button type="button" onclick="history.back()" class="btn-back">← 뒤로</button>
                </form>
            <?php else: ?>
                <button type="button" onclick="history.back()" class="btn-back">← 뒤로</button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>