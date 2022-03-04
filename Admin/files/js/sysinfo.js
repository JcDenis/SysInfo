/*global $, dotclear, CodeMirror */
'use strict';

$(() => {
  dotclear.mergeDeep(dotclear, dotclear.getData('sysinfo'));

  const dotclearAjax = (method, args, fn, msg = '') => {
    let content = null;
    const params = {
      xd_check: dotclear.nonce,
      f: method,
    };
    $.extend(params, args);
    const promise = $.ajax({
      url: 'services.php',
      data: params,
      dataType: 'xml',
    });
    promise.done((data) => {
      if ($('rsp[status=failed]', data).length > 0) {
        // For debugging purpose only:
        // console.log($('rsp',data).attr('message'));
        window.console.log('Dotclear REST server error');
        return;
      }
      // ret -> status (true/false)
      // msg -> REST method return value
      const ret = Number($('rsp>sysinfo', data).attr('ret'));
      content = $('rsp>sysinfo', data).attr('msg');
      if (ret && fn !== undefined && typeof fn === 'function') {
        // Call callback function with returned value
        fn(content);
      }
      if (!ret && msg !== '') {
        window.alert(msg);
      }
    });
  };

  const getStaticCacheFilename = (url, fn) => dotclearAjax('getStaticCacheName', { url }, fn, '');

  const loadStaticCacheDirs = (dir, fn) => dotclearAjax('getStaticCacheDir', { root: dir }, fn, '');

  const loadStaticCacheList = (dir, fn) => dotclearAjax('getStaticCacheList', { root: dir }, fn, '');

  const loadServerFile = (filename, type, fn) => {
    switch (type) {
      case 'tpl':
        dotclearAjax('getCompiledTemplate', { file: filename }, fn, dotclear.msg.tpl_not_found);
        break;
      case 'sc':
        dotclearAjax('getStaticCacheFile', { file: filename }, fn, dotclear.msg.sc_not_found);
        break;
    }
  };

  const viewSource = (prefix, filename, content) => {
    let cm_editor; // Codemirror instance
    const src = `<div class="${prefix}_view"><h1>${filename}</h1><textarea id="${prefix}_source">${JSON.parse(
      window.atob(content)
    )}</textarea></div>`;
    $.magnificPopup.open({
      items: {
        src,
        type: 'inline',
      },
      callbacks: {
        open: () => {
          if (dotclear.colorsyntax) {
            // Popup opened, format textarea with codemirror
            const options = {
              mode: 'text/html', // 'application/x-httpd-php',
              tabMode: 'indent',
              lineWrapping: 'true',
              lineNumbers: 'true',
              matchBrackets: 'true',
              autoCloseBrackets: 'true',
              readOnly: 'true',
            };
            if (dotclear.colorsyntax_theme !== '') {
              options.theme = dotclear.colorsyntax_theme;
            }
            const textarea = document.getElementById(`${prefix}_source`);
            cm_editor = CodeMirror.fromTextArea(textarea, options);
          }
        },
        close: () => {
          if (cm_editor != null) {
            // Remove Codemirror instance
            cm_editor.toTextArea();
          }
        },
      },
    });
  };

  // Compiled template preview
  $('a.tpl_compiled').on('click', (e) => {
    e.preventDefault();
    const template_file = $(e.target).text();
    // Open template file content in a modal iframe
    if (template_file !== undefined) {
      loadServerFile(template_file, 'tpl', (content) => {
        viewSource('tpl_compiled', template_file, content);
      });
    }
  });

  // Static cache dir expand (load 2nd level subdirs via Ajax)
  $('a.sc_dir').on('click', (e) => {
    e.preventDefault();
    const main_dir = $(e.target).text();
    loadStaticCacheDirs(main_dir, (dirs) => {
      // Insert list and remove previous raw
      const r = $(e.target).parent().parent();
      r.after(dirs).remove();
      // Static cache subdir expand (load 3rd level subdirs and cache file list via Ajax)
      $('a.sc_subdir').on('click', (f) => {
        f.preventDefault();
        const sub_dir = $(f.target).text();
        loadStaticCacheList(`${main_dir}/${sub_dir}`, (list) => {
          // Insert list and remove previous raw
          $('a.sc_compiled').off('click');
          const s = $(f.target).parent().parent();
          s.after(list).remove();
          dotclear.condSubmit('#scform td input[type=checkbox]', '#scform #delscaction');
          // Static cache file preview
          $('a.sc_compiled').on('click', (g) => {
            g.preventDefault();
            const cache_file = $(g.target).attr('data-file');
            // Open static cache file content in a modal iframe
            if (cache_file !== undefined) {
              loadServerFile(cache_file, 'sc', (content) => {
                viewSource('sc_compiled', $(g.target).text(), content);
              });
            }
          });
        });
      });
    });
  });

  // Autosubmit on checklist change
  $('#checklist').on('change', function () {
    this.form.submit();
  });

  // Static cache calculator
  $('#getscaction').on('click', (e) => {
    e.preventDefault();
    $('#sccalc_res').text('');
    $('#sccalc_preview').text('').off('click');
    const url = $('#sccalc_url').val();
    if (url !== undefined && url !== '') {
      getStaticCacheFilename(url, (res) => {
        const text = `${String.fromCharCode(160) + res.slice(0, 2)} / ${res.slice(2, 4)} / ${res.slice(4, 6)} / `;
        $('#sccalc_res').text(text);
        $('#sccalc_preview').text(res).trigger('focus');
        $('#sccalc_preview').on('click', (f) => {
          f.preventDefault();
          const cache_file = `${res.slice(0, 2)}/${res.slice(2, 4)}/${res.slice(4, 6)}/${res}`;
          loadServerFile(`${$(f.target).attr('data-dir')}/${cache_file}`, 'sc', (content) => {
            viewSource('sc_compiled', res, content);
          });
        });
      });
    }
  });

  // Checkboxes helpers

  // Template cache files
  $('#tplform .checkboxes-helpers').each(function () {
    dotclear.checkboxesHelpers(this, undefined, '#tplform td input[type=checkbox]:enabled', '#tplform #deltplaction');
  });
  $('#tplform td input[type=checkbox]').enableShiftClick();
  dotclear.condSubmit('#tplform td input[type=checkbox]', '#tplform #deltplaction');
  $('form input[type=submit][name=deltplaction]').on('click', () => window.confirm(dotclear.msg.confirm_del_tpl));

  // Static cache files
  $('#scform .checkboxes-helpers').each(function () {
    dotclear.checkboxesHelpers(this, undefined, '#scform td input[type=checkbox]:enabled', '#scform #delscaction');
  });
  $('#scform td input[type=checkbox]').enableShiftClick();
  dotclear.condSubmit('#scform td input[type=checkbox]', '#scform #delscaction');
  $('form input[type=submit][name=delscaction]').on('click', () => window.confirm(dotclear.msg.confirm_del_sc));

  // Expand/Contract all (details)
  $('#expand-all').on('click', function (e) {
    e.preventDefault();
    if ($(this).attr('open')) {
      // Close all
      $('#content details').each(function () {
        $(this).attr('open', false);
      });
    } else {
      // Open all
      $('#content details').each(function () {
        $(this).attr('open', true);
      });
    }
  });
});
