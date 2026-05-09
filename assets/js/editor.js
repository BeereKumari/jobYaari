(function() {
    function initEditor(editorId) {
        var wrapper = document.getElementById(editorId + '-wrapper');
        if (!wrapper) return;

        var toolbar = wrapper.querySelector('.rich-editor-toolbar');
        var content = wrapper.querySelector('.rich-editor-content');
        var hiddenTextarea = wrapper.querySelector('textarea[name="content"]');

        if (!content || !hiddenTextarea) return;

        function syncContent() {
            hiddenTextarea.value = content.innerHTML;
        }

        content.addEventListener('input', syncContent);

        content.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key.toLowerCase()) {
                    case 'b': e.preventDefault(); document.execCommand('bold'); break;
                    case 'i': e.preventDefault(); document.execCommand('italic'); break;
                    case 'u': e.preventDefault(); document.execCommand('underline'); break;
                    case 'z': e.preventDefault(); document.execCommand('undo'); break;
                    case 'y': e.preventDefault(); document.execCommand('redo'); break;
                    case 'k': e.preventDefault(); insertLink(); break;
                    case 'l':
                        if (e.shiftKey) {
                            e.preventDefault();
                            document.execCommand('insertOrderedList');
                        }
                        break;
                }
            }
        });

        toolbar.addEventListener('click', function(e) {
            var btn = e.target.closest('button[data-command]');
            if (!btn) return;

            var cmd = btn.getAttribute('data-command');
            var value = btn.getAttribute('data-value');

            content.focus();

            if (cmd === 'insertTable') {
                showTableModal();
                return;
            }

            if (cmd === 'insertTemplateTable') {
                showTemplateModal();
                return;
            }

            if (cmd === 'createLink') {
                insertLink();
                return;
            }

            if (cmd === 'removeFormat') {
                document.execCommand('removeFormat');
                document.execCommand('unlink');
                return;
            }

            if (cmd === 'formatBlock') {
                var val = value || toolbar.querySelector('select[data-command="formatBlock"]').value;
                if (val === 'blockquote') {
                    document.execCommand('formatBlock', false, '<blockquote>');
                } else {
                    document.execCommand('formatBlock', false, '<' + val + '>');
                }
                updateButtonStates();
                return;
            }

            if (cmd === 'fontSize') {
                var size = toolbar.querySelector('select[data-command="fontSize"]').value;
                document.execCommand('fontSize', false, size);
                return;
            }

            if (cmd === 'foreColor' || cmd === 'hiliteColor') {
                var picker = btn.querySelector('input[type="color"]');
                if (picker) {
                    picker.click();
                    picker.oninput = function() {
                        document.execCommand(cmd, false, picker.value);
                        var indicator = btn.querySelector('.color-indicator');
                        if (indicator) indicator.style.background = picker.value;
                    };
                }
                return;
            }

            document.execCommand(cmd, false, null);
            updateButtonStates();
        });

        toolbar.addEventListener('change', function(e) {
            var sel = e.target;
            if (sel.getAttribute('data-command') === 'formatBlock') {
                content.focus();
                if (sel.value === 'blockquote') {
                    document.execCommand('formatBlock', false, '<blockquote>');
                } else {
                    document.execCommand('formatBlock', false, '<' + sel.value + '>');
                }
                updateButtonStates();
            }
            if (sel.getAttribute('data-command') === 'fontSize') {
                content.focus();
                document.execCommand('fontSize', false, sel.value);
            }
        });

        function updateButtonStates() {
            var buttons = toolbar.querySelectorAll('button[data-command]');
            buttons.forEach(function(btn) {
                var cmd = btn.getAttribute('data-command');
                if (['bold', 'italic', 'underline', 'strikeThrough'].indexOf(cmd) !== -1) {
                    if (document.queryCommandState(cmd)) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                }
            });
            var activeAlignment = ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'].find(function(cmd) {
                return document.queryCommandState(cmd);
            });
            toolbar.querySelectorAll('button[data-command^="justify"]').forEach(function(btn) {
                btn.classList.remove('active-align');
            });
            if (activeAlignment) {
                var activeBtn = toolbar.querySelector('button[data-command="' + activeAlignment + '"]');
                if (activeBtn) activeBtn.classList.add('active-align');
            }
        }

        content.addEventListener('mouseup', updateButtonStates);
        content.addEventListener('keyup', updateButtonStates);

        function insertLink() {
            var sel = window.getSelection();
            var selectedText = sel.toString();
            var url = prompt('Enter URL:', 'https://');
            if (url && url !== 'https://') {
                if (!selectedText) {
                    document.execCommand('insertText', false, url);
                    sel.modify('move', 'backward', 'word');
                }
                document.execCommand('createLink', false, url);
                var range = sel.getRangeAt(0);
                range.collapse(false);
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }

        function showTableModal() {
            var overlay = getOrCreateOverlay();
            overlay.innerHTML = '<div class="editor-modal">' +
                '<h3>Insert Table</h3>' +
                '<div class="modal-form">' +
                '<label>Rows: <input type="number" id="table-rows" value="4" min="1" max="30"></label>' +
                '<label>Columns: <input type="number" id="table-cols" value="3" min="1" max="10"></label>' +
                '<label><input type="checkbox" id="table-header" checked> Include Header Row</label>' +
                '<label><input type="checkbox" id="table-striped" checked> Alternating Row Colors</label>' +
                '<label><input type="checkbox" id="table-bordered" checked> Full Borders</label>' +
                '</div>' +
                '<div class="modal-actions">' +
                '<button type="button" class="btn-modal-cancel" onclick="this.closest(\'.editor-modal\').parentElement.classList.remove(\'active\')">Cancel</button>' +
                '<button type="button" class="btn-modal-insert">Insert Table</button>' +
                '</div></div>';

            overlay.classList.add('active');

            overlay.querySelector('.btn-modal-insert').addEventListener('click', function() {
                var rows = parseInt(overlay.querySelector('#table-rows').value) || 4;
                var cols = parseInt(overlay.querySelector('#table-cols').value) || 3;
                var hasHeader = overlay.querySelector('#table-header').checked;
                var striped = overlay.querySelector('#table-striped').checked;
                var bordered = overlay.querySelector('#table-bordered').checked;

                var classes = 'exam-table';
                if (striped) classes += ' table-striped';
                if (!bordered) classes += ' table-no-border';

                var table = '<table class="' + classes + '">';

                if (hasHeader) {
                    table += '<thead><tr>';
                    for (var c = 0; c < cols; c++) {
                        table += '<th>Header ' + (c + 1) + '</th>';
                    }
                    table += '</tr></thead><tbody>';
                    rows--;
                } else {
                    table += '<tbody>';
                }

                for (var r = 0; r < rows; r++) {
                    table += '<tr>';
                    for (var c2 = 0; c2 < cols; c2++) {
                        table += '<td>Cell</td>';
                    }
                    table += '</tr>';
                }
                table += '</tbody></table><p></p>';

                content.focus();
                document.execCommand('insertHTML', false, table);
                syncContent();
                overlay.classList.remove('active');
            });

            overlay.querySelector('.btn-modal-cancel').addEventListener('click', function() {
                overlay.classList.remove('active');
            });
        }

        function showTemplateModal() {
            var overlay = getOrCreateOverlay();
            overlay.innerHTML = '<div class="editor-modal editor-modal-lg">' +
                '<h3>Insert Exam Info Table Template</h3>' +
                '<p class="modal-desc">Choose a pre-formatted table template commonly used in government exam portals.</p>' +
                '<div class="template-grid">' +
                '<button type="button" data-template="exam-dates" class="template-btn"><span class="template-icon">&#128197;</span>Important Dates</button>' +
                '<button type="button" data-template="fee-structure" class="template-btn"><span class="template-icon">&#8377;</span>Application Fee</button>' +
                '<button type="button" data-template="eligibility" class="template-btn"><span class="template-icon">&#127891;</span>Eligibility Criteria</button>' +
                '<button type="button" data-template="vacancy" class="template-btn"><span class="template-icon">&#128203;</span>Vacancy Details</button>' +
                '<button type="button" data-template="selection" class="template-btn"><span class="template-icon">&#9745;</span>Selection Process</button>' +
                '<button type="button" data-template="important-links" class="template-btn"><span class="template-icon">&#128279;</span>Important Links</button>' +
                '</div>' +
                '<div class="modal-actions">' +
                '<button type="button" class="btn-modal-cancel">Close</button>' +
                '</div></div>';

            overlay.classList.add('active');

            overlay.querySelectorAll('.template-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var template = btn.getAttribute('data-template');
                    var html = getTemplate(template);
                    content.focus();
                    document.execCommand('insertHTML', false, html);
                    syncContent();
                    overlay.classList.remove('active');
                });
            });

            overlay.querySelector('.btn-modal-cancel').addEventListener('click', function() {
                overlay.classList.remove('active');
            });
        }

        function getTemplate(type) {
            var templates = {
                'exam-dates': '<h3>Important Dates</h3>' +
                    '<table class="exam-table table-striped"><thead><tr>' +
                    '<th>Event</th><th>Date</th>' +
                    '</tr></thead><tbody>' +
                    '<tr><td>Application Start Date</td><td>DD/MM/YYYY</td></tr>' +
                    '<tr><td>Application End Date</td><td>DD/MM/YYYY</td></tr>' +
                    '<tr><td>Last Date for Fee Payment</td><td>DD/MM/YYYY</td></tr>' +
                    '<tr><td>Admit Card Available From</td><td>DD/MM/YYYY</td></tr>' +
                    '<tr><td>Date of Exam</td><td>DD/MM/YYYY</td></tr>' +
                    '<tr><td>Result Declaration Date</td><td>DD/MM/YYYY</td></tr>' +
                    '</tbody></table><p></p>',

                'fee-structure': '<h3>Application Fee Details</h3>' +
                    '<table class="exam-table table-striped"><thead><tr>' +
                    '<th>Category</th><th>Application Fee</th>' +
                    '</tr></thead><tbody>' +
                    '<tr><td>General / OBC</td><td>Rs. 000/-</td></tr>' +
                    '<tr><td>SC / ST / EWS</td><td>Rs. 000/-</td></tr>' +
                    '<tr><td>Female (All Categories)</td><td>Rs. 000/-</td></tr>' +
                    '<tr><td>PH (Divyangjan)</td><td>Rs. 000/-</td></tr>' +
                    '</tbody></table>' +
                    '<p><strong>Payment Mode:</strong> Online (Credit Card / Debit Card / Net Banking / UPI)</p><p></p>',

                'eligibility': '<h3>Eligibility Criteria</h3>' +
                    '<table class="exam-table table-striped"><thead><tr>' +
                    '<th>Criteria</th><th>Details</th>' +
                    '</tr></thead><tbody>' +
                    '<tr><td>Age Limit</td><td>Min: XX years | Max: XX years</td></tr>' +
                    '<tr><td>Age Relaxation</td><td>As per Govt. norms (SC/ST/OBC/PwD)</td></tr>' +
                    '<tr><td>Education Qualification</td><td>Bachelor\'s / Master\'s Degree from recognized university</td></tr>' +
                    '<tr><td>Experience Required</td><td>XX years in relevant field</td></tr>' +
                    '<tr><td>Nationality</td><td>Indian</td></tr>' +
                    '</tbody></table><p></p>',

                'vacancy': '<h3>Vacancy Details</h3>' +
                    '<table class="exam-table table-striped"><thead><tr>' +
                    '<th>Post Name</th><th>Vacancies</th><th>Category-wise</th>' +
                    '</tr></thead><tbody>' +
                    '<tr><td>Post Name 1</td><td>000</td><td>UR: 00 | OBC: 00 | SC: 00 | ST: 00 | EWS: 00</td></tr>' +
                    '<tr><td>Post Name 2</td><td>000</td><td>UR: 00 | OBC: 00 | SC: 00 | ST: 00 | EWS: 00</td></tr>' +
                    '<tr><td>Post Name 3</td><td>000</td><td>UR: 00 | OBC: 00 | SC: 00 | ST: 00 | EWS: 00</td></tr>' +
                    '<tr><td><strong>Total</strong></td><td><strong>0000</strong></td><td><strong>UR: 00 | OBC: 00 | SC: 00 | ST: 00 | EWS: 00</strong></td></tr>' +
                    '</tbody></table><p></p>',

                'selection': '<h3>Selection Process</h3>' +
                    '<table class="exam-table table-striped"><thead><tr>' +
                    '<th>Stage</th><th>Details</th><th>Marks</th>' +
                    '</tr></thead><tbody>' +
                    '<tr><td>Stage 1: Written Exam (CBT)</td><td>Objective type questions</td><td>200</td></tr>' +
                    '<tr><td>Stage 2: Skill Test / Typing</td><td>Qualifying nature</td><td>--- </td></tr>' +
                    '<tr><td>Stage 3: Document Verification</td><td>Original documents check</td><td>--- </td></tr>' +
                    '<tr><td>Stage 4: Medical Examination</td><td>As per post requirements</td><td>--- </td></tr>' +
                    '</tbody></table><p><em>Note: All stages are mandatory. Candidates must qualify in each stage.</em></p><p></p>',

                'important-links': '<h3>Important Links</h3>' +
                    '<table class="exam-table table-striped"><thead><tr>' +
                    '<th>Description</th><th>Link</th>' +
                    '</tr></thead><tbody>' +
                    '<tr><td>Apply Online</td><td><a href="#">Click Here</a></td></tr>' +
                    '<tr><td>Download Notification</td><td><a href="#">Click Here</a></td></tr>' +
                    '<tr><td>Download Syllabus</td><td><a href="#">Click Here</a></td></tr>' +
                    '<tr><td>Official Website</td><td><a href="#">Click Here</a></td></tr>' +
                    '<tr><td>Admit Card</td><td><a href="#">Click Here</a></td></tr>' +
                    '<tr><td>Result</td><td><a href="#">Click Here</a></td></tr>' +
                    '</tbody></table><p></p>'
            };
            return templates[type] || '';
        }

        function getOrCreateOverlay() {
            var overlay = document.getElementById('editor-modal-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'editor-modal-overlay';
                overlay.className = 'modal-overlay';
                document.body.appendChild(overlay);
            }
            return overlay;
        }

        hiddenTextarea.value = content.innerHTML;
    }

    window.initEditor = initEditor;
})();
