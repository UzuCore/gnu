<?php
include_once('./_common.php');
include_once(G5_PATH . '/head.php');
include_once(G5_PATH . '/search.config.php');

$search_stx = isset($_GET['stx']) ? trim($_GET['stx']) : '';
$page_jump = $is_mobile ? 3 : 10;
$page_display_count = $is_mobile ? 3 : 10;

$options = [
    'stx' => $search_stx,
    'bo_table' => isset($_GET['bo_table']) ? trim($_GET['bo_table']) : '',
    'page' => isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1,
    'sort' => $_GET['sort'] ?? 'date_desc',
    'search_type' => $_GET['search_type'] ?? 'subject_content',
    'rows' => 15,
    'page_jump' => $page_jump,
    'page_display_count' => $page_display_count,
    'excluded_bo_tables' => $excluded_bo_tables,
    'excluded_ids' => $excluded_ids,
    'excluded_names' => $excluded_names,
    'exclude_words' => $exclude_words,
    'use_external' => $use_external,
    'external_db' => $external_db,
];
$options['offset'] = ($options['page'] - 1) * $options['rows'];

include_once(G5_PATH . '/search.lib.php');
$board_list = get_board_list();
?>
<style>
.search-wrap {
    max-width: 1025px;
    margin: 0 auto;
    padding: 0 10px;
}

.search-form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
    align-items: center;
}

.search-form input[type="text"] {
    padding: 9px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 3px;
    flex-grow: 2;
    min-width: 300px;
    height: 36px;
}

.search-form select,
.search-form button {
    padding: 6px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 3px;
    height: 36px;
}

.search-form button {
    background-color: #225577;
    border: 1px solid #225577;
    color: white;
    font-weight: bold;
    cursor: pointer;
}
.search-form button:hover {
    background-color: #113355;
}

@media (max-width: 768px) {
    .search-form {
        flex-direction: column;
        align-items: stretch;
    }

    .search-form input,
    .search-form button {
        width: 100%;
    }

    .search-form .select-group {
        display: flex;
        gap: 8px;
        justify-content: space-between;
    }

    .search-form .select-group select {
        flex: 1;
    }
}

ul.results { margin: 0; padding: 0; list-style: none; }
.results li { padding: 22px 0; border-bottom: 1px solid #eee; min-height: 62px; }
.results li .title { font-weight: bold; font-size: 1.05rem; color: #225577; text-decoration: none; }
.results li .content-preview { font-size: 1rem; color: #444; margin-top: 4px; }
.no-content { color: #999; font-style: italic; }

mark {
    background: #ff005a;
    color: #fff;
    font-weight: bold;
    padding: 2px 5px 3px;
    margin: 0 2px;
}

.notice-max-result {
    margin: 5px 0 15px 0;
    background: #ffeaea;
    border: 1px solid #ff6b81;
    color: #c22;
    font-size: .97em;
    padding: 8px 13px;
    border-radius: 5px;
    display: inline-block;
}

.pagination {
  display: flex;
  gap: 8px;
  margin: 20px 0 24px 0;
  align-items: center;
  justify-content: flex-start;
  font-family: inherit;
}

.pagination a,
.pagination span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  height: 38px;
  padding: 0 8px;
  background: #f2f3f6;
  color: #666;
  font-size: 1.10rem;
  border-radius: 8px;
  border: none;
  font-weight: 500;
  cursor: pointer;
  user-select: none;
  text-decoration: none;
  transition: background 0.12s, color 0.12s;
}

.pagination a.current,
.pagination span.current {
  background: #288aff;
  color: #fff;
  font-weight: bold;
  cursor: default;
  pointer-events: none;
}

.pagination .disabled {
  background: #f2f3f6;
  color: #c5c7cd;
  cursor: not-allowed;
  pointer-events: none;
  font-weight: normal;
}

.pagination a:not(.current):not(.disabled):hover {
  background: #e6f0ff;
  color: #288aff;
}

/* 모바일 대응 (원하는대로 수정 가능) */
@media (max-width: 600px) {
  .pagination a,
  .pagination span {
    min-width: 32px;
    height: 32px;
    font-size: 1rem;
    padding: 0 4px;
    border-radius: 5px;
  }
}


.dark-mode .results li { border-bottom: 1px solid #2c2c2c }
.dark-mode .results li .title { color: #557799 }
.dark-mode .results li .content-preview { color:#fff }
.dark-mode .no-content { color: #999 }
.dark-mode .search-form input,
.dark-mode .search-form select {
    border: 1px solid #2c2c2c;
    background: #2c2c2c;
    color: #999;
}



/* 스켈레톤 */
.skeleton-list { margin: 0; padding: 0; }
.skeleton-item { display: flex; flex-direction: column; gap: 6px; padding: 22px 0; border-bottom: 1px solid #eee; min-height: 62px; }
.skeleton-title, .skeleton-content, .skeleton-meta { background: #e7e7ec; border-radius: 3px; }
.skeleton-title { width: 40%; height: 18px; animation: skeleton 1s infinite alternate; }
.skeleton-content { width: 100%; height: 15px; animation: skeleton 1s .1s infinite alternate; }
.skeleton-meta { width: 22%; height: 12px; margin-top: 3px; animation: skeleton 1s .2s infinite alternate; }

@keyframes skeleton { from { opacity: 0.6; } to { opacity: 0.3; } }
</style>

<div class="search-wrap">
    <form method="get" class="search-form" id="searchForm">
        <input type="text" name="stx" value="<?=htmlspecialchars($search_stx)?>" placeholder="두 글자 이상의 키워드를 입력하세요">
        <div class="select-group">
            <select name="bo_table">
                <option value="">전체게시판</option>
                <?php foreach($board_list as $id => $subject): ?>
                    <?php if (in_array($id, $excluded_bo_tables)) continue; ?>
                    <option value="<?=htmlspecialchars($id)?>" <?=($options['bo_table'] === $id) ? 'selected' : ''?>><?=htmlspecialchars($subject)?></option>
                <?php endforeach; ?>
            </select>
            <select name="search_type">
                <option value="subject_content" <?=($options['search_type'] == 'subject_content') ? 'selected' : ''?>>게시물</option>
                <option value="subject_only" <?=($options['search_type'] == 'subject_only') ? 'selected' : ''?>>제목</option>
                <option value="content_only" <?=($options['search_type'] == 'content_only') ? 'selected' : ''?>>내용</option>
                <option value="mb_id" <?=($options['search_type'] == 'mb_id') ? 'selected' : ''?>>회원아이디</option>
                <option value="wr_name" <?=($options['search_type'] == 'wr_name') ? 'selected' : ''?>>글쓴이</option>
            </select>
            <select name="sort">
                <option value="date_desc" <?=($options['sort'] == 'date_desc') ? 'selected' : ''?>>최신순</option>
                <option value="date_asc" <?=($options['sort'] == 'date_asc') ? 'selected' : ''?>>오래된순</option>
                <?php if (in_array($options['search_type'], ['subject_content', 'subject_only', 'content_only'])): ?>
                    <option value="relevance" <?=($options['sort'] == 'relevance') ? 'selected' : ''?>>정확도순</option>
                <?php endif; ?>
            </select>
        </div>
        <button type="submit">검색</button>
    </form>

    <div id="search-top-message" style="margin-bottom:16px; display:none;"></div>
    <ul class="results" id="results-list"></ul>
    <div id="search-pagination"></div>

    <ul id="search-skeleton" class="skeleton-list" style="display:none;">
        <?php for($i=0; $i<6; $i++): ?>
            <li class="skeleton-item">
                <div class="skeleton-title"></div>
                <div class="skeleton-content"></div>
                <div class="skeleton-meta"></div>
            </li>
        <?php endfor; ?>
    </ul>
</div>

<script>
function showSkeleton() {
  $('#search-skeleton').show();
  $('#results-list').hide();
  $('#search-pagination').hide();
}
function hideSkeleton() {
  $('#search-skeleton').hide();
  $('#results-list').show();
  $('#search-pagination').show();
}
function setTopMessage(msgHtml) {
  if (msgHtml) {
    $('#search-top-message').html(msgHtml).show();
  } else {
    $('#search-top-message').hide();
  }
}
function fetchSearch(page) {
  showSkeleton();
  let data = $('#searchForm').serializeArray();
  if (page) data.push({ name: 'page', value: page });
  $.ajax({
    url: 'search.ajax.php',
    data: $.param(data),
    dataType: 'json',
    success: function(data) {
      let html = '';
      if (data.items.length === 0) {
        html = '<li>검색 결과가 없습니다.</li>';
      } else {
        $.each(data.items, function(i, row) {
          html += '<li><a class="title" href="' + row.url + '">' + row.subject + '</a>';
          html += row.is_comment ? '&nbsp;' : '<br>';
          html += '<span class="content-preview">' + row.preview + '</span><br><small>' + row.name + ' / ' + row.date + '</small></li>';
        });
      }
      $('#results-list').html(html);
      $('#search-pagination').html(data.pagination);

      let msg = `<span style="font-size:.9rem;">검색 결과: ${data.show_count}건</span>`;
      if (data.notice) msg += `<div class="notice-max-result">${data.notice}</div>`;
      setTopMessage(msg);

      hideSkeleton();
    },
    error: function() {
      $('#results-list').html('<li>검색 중 오류가 발생했습니다.</li>');
      hideSkeleton();
    }
  });
}
$('#searchForm').on('submit', function(e) {
  e.preventDefault();
  fetchSearch(1);
});
$(document).on('click', '.page-link', function(e) {
  e.preventDefault();
  var p = $(this).data('page');
  if (p) fetchSearch(p);
});
$(function() {
  fetchSearch(<?=$options['page']?>);
});
</script>
<?php include_once(G5_PATH . '/tail.php'); ?>
