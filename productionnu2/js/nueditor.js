

// Setup a main editor instance
function nuEditor(id) {    
    // Create main editor
    var mainEditor = CodeMirror($('#' + id)[0], {
            indentUnit: 4,
            enterMode: 'keep',
            tabMode: 'indent',
            lineNumbers: true
        }
    );
    
    // Setup tabs
    $('.editor-nav li').click(function() {
        // Get the hidden element
        $elem = $('#'+this.id.replace(/^tab_/, ''));
        
        // Make this the active tab
        $('.editor-nav li.active').removeClass('active');
        $(this).addClass('active');
        
        // Dump the element's contents into the editor
        mainEditor.setValue($elem.val());
        // Set the syntax mode
        mainEditor.setOption('mode', $(this).data('mode'));
        // Save the editor value back into the hidden field
        mainEditor.setOption('onBlur', function(){
            $elem.val(mainEditor.getValue());
        });
    });
    
    // Activate the first tab
    $('.editor-nav li:first').click();    
}

// Setup an inline editor instance
function nuInlineEditor(id, mode){
    var $ta = $('#' + id);
    
    // Create the editor instance
    var editor = CodeMirror.fromTextArea($ta[0], {
        mode: mode
    });
    
    editor.setOption('onBlur', function(){
        // Make sure nuBuilder knows the field should be updated
        uDB($ta[0], 'text');
        // Untick the row if the editor is in a subform
        $ta.parents('div[id^=rowdiv_]').find('input[type=checkbox]').attr('checked', false);
        // Save the editor value back into the hidden field
        editor.save();
    });
    
    // Correct editor width / height to be similar to original textarea
    $ta.next('.CodeMirror').addClass('inline-editor ' + $ta.attr('class')).find('.CodeMirror-scroll').height(
        (parseInt($ta.attr('rows')) + 1) + 'em'
    ).width($ta.width());
}

// codemirror.js

// CodeMirror is the only global var we claim
var CodeMirror = (function() {
  // This is the function that produces an editor instance. It's
  // closure is used to store the editor state.
  function CodeMirror(place, givenOptions) {
    // Determine effective options based on given values and defaults.
    var options = {}, defaults = CodeMirror.defaults;
    for (var opt in defaults)
      if (defaults.hasOwnProperty(opt))
        options[opt] = (givenOptions && givenOptions.hasOwnProperty(opt) ? givenOptions : defaults)[opt];

    var targetDocument = options["document"];
    // The element in which the editor lives.
    var wrapper = targetDocument.createElement("div");
    wrapper.className = "CodeMirror";
    // This mess creates the base DOM structure for the editor.
    wrapper.innerHTML =
      '<div style="overflow: hidden; position: relative; width: 1px; height: 0px;">' + // Wraps and hides input textarea
        '<textarea style="position: absolute; width: 2px;" wrap="off"></textarea></div>' +
      '<div class="CodeMirror-scroll cm-s-' + options.theme + '">' +
        '<div style="position: relative">' + // Set to the height of the text, causes scrolling
          '<div style="position: absolute; height: 0; width: 0; overflow: hidden;"></div>' +
          '<div style="position: relative">' + // Moved around its parent to cover visible view
            '<div class="CodeMirror-gutter"><div class="CodeMirror-gutter-text"></div></div>' +
            // Provides positioning relative to (visible) text origin
            '<div class="CodeMirror-lines"><div style="position: relative">' +
              '<pre class="CodeMirror-cursor">&#160;</pre>' + // Absolutely positioned blinky cursor
              '<div></div>' + // This DIV contains the actual code
            '</div></div></div></div></div>';
    if (place.appendChild) place.appendChild(wrapper); else place(wrapper);
    // I've never seen more elegant code in my life.
    var inputDiv = wrapper.firstChild, input = inputDiv.firstChild,
        scroller = wrapper.lastChild, code = scroller.firstChild,
        measure = code.firstChild, mover = measure.nextSibling,
        gutter = mover.firstChild, gutterText = gutter.firstChild,
        lineSpace = gutter.nextSibling.firstChild,
        cursor = lineSpace.firstChild, lineDiv = cursor.nextSibling;
    if (options.tabindex != null) input.tabindex = options.tabindex;
    if (!options.gutter && !options.lineNumbers) gutter.style.display = "none";

    // Delayed object wrap timeouts, making sure only one is active. blinker holds an interval.
    var poll = new Delayed(), highlight = new Delayed(), blinker;

    // mode holds a mode API object. lines an array of Line objects
    // (see Line constructor), work an array of lines that should be
    // parsed, and history the undo history (instance of History
    // constructor).
    var mode, lines = [new Line("")], work, history = new History(), focused;
    loadMode();
    // The selection. These are always maintained to point at valid
    // positions. Inverted is used to remember that the user is
    // selecting bottom-to-top.
    var sel = {from: {line: 0, ch: 0}, to: {line: 0, ch: 0}, inverted: false};
    // Selection-related flags. shiftSelecting obviously tracks
    // whether the user is holding shift. reducedSelection is a hack
    // to get around the fact that we can't create inverted
    // selections. See below.
    var shiftSelecting, reducedSelection, lastDoubleClick;
    // Variables used by startOperation/endOperation to track what
    // happened during the operation.
    var updateInput, changes, textChanged, selectionChanged, leaveInputAlone;
    // Current visible range (may be bigger than the view window).
    var showingFrom = 0, showingTo = 0, lastHeight = 0, curKeyId = null;
    // editing will hold an object describing the things we put in the
    // textarea, to help figure out whether something changed.
    // bracketHighlighted is used to remember that a backet has been
    // marked.
    var editing, bracketHighlighted;
    // Tracks the maximum line length so that the horizontal scrollbar
    // can be kept static when scrolling.
    var maxLine = "", maxWidth;

    // Initialize the content.
    operation(function(){setValue(options.value || ""); updateInput = false;})();

    // Register our event handlers.
    connect(scroller, "mousedown", operation(onMouseDown));
    // Gecko browsers fire contextmenu *after* opening the menu, at
    // which point we can't mess with it anymore. Context menu is
    // handled in onMouseDown for Gecko.
    if (!gecko) connect(scroller, "contextmenu", onContextMenu);
    connect(code, "dblclick", operation(onDblClick));
    connect(scroller, "scroll", function() {updateDisplay([]); if (options.onScroll) options.onScroll(instance);});
    connect(window, "resize", function() {updateDisplay(true);});
    connect(input, "keyup", operation(onKeyUp));
    connect(input, "keydown", operation(onKeyDown));
    connect(input, "keypress", operation(onKeyPress));
    connect(input, "focus", onFocus);
    connect(input, "blur", onBlur);

    connect(scroller, "dragenter", e_stop);
    connect(scroller, "dragover", e_stop);
    connect(scroller, "drop", operation(onDrop));
    connect(scroller, "paste", function(){focusInput(); fastPoll();});
    connect(input, "paste", function(){fastPoll();});
    connect(input, "cut", function(){fastPoll();});
    
    // IE throws unspecified error in certain cases, when 
    // trying to access activeElement before onload
    var hasFocus; try { hasFocus = (targetDocument.activeElement == input); } catch(e) { }
    if (hasFocus) setTimeout(onFocus, 20);
    else onBlur();

    function isLine(l) {return l >= 0 && l < lines.length;}
    // The instance object that we'll return. Mostly calls out to
    // local functions in the CodeMirror function. Some do some extra
    // range checking and/or clipping. operation is used to wrap the
    // call so that changes it makes are tracked, and the display is
    // updated afterwards.
    var instance = {
      getValue: getValue,
      setValue: operation(setValue),
      getSelection: getSelection,
      replaceSelection: operation(replaceSelection),
      focus: function(){focusInput(); onFocus(); fastPoll();},
      setOption: function(option, value) {
        options[option] = value;
        if (option == "lineNumbers" || option == "gutter" || option == "firstLineNumber") gutterChanged();
        else if (option == "mode" || option == "indentUnit") loadMode();
        else if (option == "readOnly" && value == "nocursor") input.blur();
        else if (option == "theme") scroller.className = scroller.className.replace(/cm-s-\w+/, "cm-s-" + value);
      },
      getOption: function(option) {return options[option];},
      undo: operation(undo),
      redo: operation(redo),
      indentLine: operation(function(n, dir) {
        if (isLine(n)) indentLine(n, dir == null ? "smart" : dir ? "add" : "subtract");
      }),
      historySize: function() {return {undo: history.done.length, redo: history.undone.length};},
      matchBrackets: operation(function(){matchBrackets(true);}),
      getTokenAt: function(pos) {
        pos = clipPos(pos);
        return lines[pos.line].getTokenAt(mode, getStateBefore(pos.line), pos.ch);
      },
      getStateAfter: function(line) {
        line = clipLine(line == null ? lines.length - 1: line);
        return getStateBefore(line + 1);
      },
      cursorCoords: function(start){
        if (start == null) start = sel.inverted;
        return pageCoords(start ? sel.from : sel.to);
      },
      charCoords: function(pos){return pageCoords(clipPos(pos));},
      coordsChar: function(coords) {
        var off = eltOffset(lineSpace);
        var line = clipLine(Math.min(lines.length - 1, showingFrom + Math.floor((coords.y - off.top) / lineHeight())));
        return clipPos({line: line, ch: charFromX(clipLine(line), coords.x - off.left)});
      },
      getSearchCursor: function(query, pos, caseFold) {return new SearchCursor(query, pos, caseFold);},
      markText: operation(function(a, b, c){return operation(markText(a, b, c));}),
      setMarker: addGutterMarker,
      clearMarker: removeGutterMarker,
      setLineClass: operation(setLineClass),
      lineInfo: lineInfo,
      addWidget: function(pos, node, scroll, where) {
        pos = localCoords(clipPos(pos));
        var top = pos.yBot, left = pos.x;
        node.style.position = "absolute";
        code.appendChild(node);
        node.style.left = left + "px";
        if (where == "over") top = pos.y;
        else if (where == "near") {
          var vspace = Math.max(scroller.offsetHeight, lines.length * lineHeight()),
              hspace = Math.max(code.clientWidth, lineSpace.clientWidth) - paddingLeft();
          if (pos.yBot + node.offsetHeight > vspace && pos.y > node.offsetHeight)
            top = pos.y - node.offsetHeight;
          if (left + node.offsetWidth > hspace)
            left = hspace - node.offsetWidth;
        }
        node.style.top = (top + paddingTop()) + "px";
        node.style.left = (left + paddingLeft()) + "px";
        if (scroll)
          scrollIntoView(left, top, left + node.offsetWidth, top + node.offsetHeight);
      },

      lineCount: function() {return lines.length;},
      getCursor: function(start) {
        if (start == null) start = sel.inverted;
        return copyPos(start ? sel.from : sel.to);
      },
      somethingSelected: function() {return !posEq(sel.from, sel.to);},
      setCursor: operation(function(line, ch) {
        if (ch == null && typeof line.line == "number") setCursor(line.line, line.ch);
        else setCursor(line, ch);
      }),
      setSelection: operation(function(from, to) {setSelection(clipPos(from), clipPos(to || from));}),
      getLine: function(line) {if (isLine(line)) return lines[line].text;},
      setLine: operation(function(line, text) {
        if (isLine(line)) replaceRange(text, {line: line, ch: 0}, {line: line, ch: lines[line].text.length});
      }),
      removeLine: operation(function(line) {
        if (isLine(line)) replaceRange("", {line: line, ch: 0}, clipPos({line: line+1, ch: 0}));
      }),
      replaceRange: operation(replaceRange),
      getRange: function(from, to) {return getRange(clipPos(from), clipPos(to));},

      operation: function(f){return operation(f)();},
      refresh: function(){updateDisplay(true);},
      getInputField: function(){return input;},
      getWrapperElement: function(){return wrapper;},
      getScrollerElement: function(){return scroller;},
      getGutterElement: function(){return gutter;}
    };

    function setValue(code) {
      history = null;
      var top = {line: 0, ch: 0};
      updateLines(top, {line: lines.length - 1, ch: lines[lines.length-1].text.length},
                  splitLines(code), top, top);
      history = new History();
    }
    function getValue(code) {
      var text = [];
      for (var i = 0, l = lines.length; i < l; ++i)
        text.push(lines[i].text);
      return text.join("\n");
    }

    function onMouseDown(e) {
      // Check whether this is a click in a widget
      for (var n = e_target(e); n != wrapper; n = n.parentNode)
        if (n.parentNode == code && n != mover) return;
      var ld = lastDoubleClick; lastDoubleClick = null;
      // First, see if this is a click in the gutter
      for (var n = e_target(e); n != wrapper; n = n.parentNode)
        if (n.parentNode == gutterText) {
          if (options.onGutterClick)
            options.onGutterClick(instance, indexOf(gutterText.childNodes, n) + showingFrom);
          return e_preventDefault(e);
        }

      var start = posFromMouse(e);
      
      switch (e_button(e)) {
      case 3:
        if (gecko && !mac) onContextMenu(e);
        return;
      case 2:
        if (start) setCursor(start.line, start.ch, true);
        return;
      }
      // For button 1, if it was clicked inside the editor
      // (posFromMouse returning non-null), we have to adjust the
      // selection.
      if (!start) {if (e_target(e) == scroller) e_preventDefault(e); return;}

      if (!focused) onFocus();
      e_preventDefault(e);
      if (ld && +new Date - ld < 400) return selectLine(start.line);

      setCursor(start.line, start.ch, true);
      var last = start, going;
      // And then we have to see if it's a drag event, in which case
      // the dragged-over text must be selected.
      function end() {
        focusInput();
        updateInput = true;
        move(); up();
      }
      function extend(e) {
        var cur = posFromMouse(e, true);
        if (cur && !posEq(cur, last)) {
          if (!focused) onFocus();
          last = cur;
          setSelectionUser(start, cur);
          updateInput = false;
          var visible = visibleLines();
          if (cur.line >= visible.to || cur.line < visible.from)
            going = setTimeout(operation(function(){extend(e);}), 150);
        }
      }

      var move = connect(targetDocument, "mousemove", operation(function(e) {
        clearTimeout(going);
        e_preventDefault(e);
        extend(e);
      }), true);
      var up = connect(targetDocument, "mouseup", operation(function(e) {
        clearTimeout(going);
        var cur = posFromMouse(e);
        if (cur) setSelectionUser(start, cur);
        e_preventDefault(e);
        end();
      }), true);
    }
    function onDblClick(e) {
      var pos = posFromMouse(e);
      if (!pos) return;
      selectWordAt(pos);
      e_preventDefault(e);
      lastDoubleClick = +new Date;
    }
    function onDrop(e) {
      e.preventDefault();
      var pos = posFromMouse(e, true), files = e.dataTransfer.files;
      if (!pos || options.readOnly) return;
      if (files && files.length && window.FileReader && window.File) {
        function loadFile(file, i) {
          var reader = new FileReader;
          reader.onload = function() {
            text[i] = reader.result;
            if (++read == n) replaceRange(text.join(""), clipPos(pos), clipPos(pos));
          };
          reader.readAsText(file);
        }
        var n = files.length, text = Array(n), read = 0;
        for (var i = 0; i < n; ++i) loadFile(files[i], i);
      }
      else {
        try {
          var text = e.dataTransfer.getData("Text");
          if (text) replaceRange(text, pos, pos);
        }
        catch(e){}
      }
    }
    function onKeyDown(e) {
      if (!focused) onFocus();

      var code = e.keyCode;
      // IE does strange things with escape.
      if (ie && code == 27) { e.returnValue = false; }
      // Tries to detect ctrl on non-mac, cmd on mac.
      var mod = (mac ? e.metaKey : e.ctrlKey) && !e.altKey, anyMod = e.ctrlKey || e.altKey || e.metaKey;
      if (code == 16 || e.shiftKey) shiftSelecting = shiftSelecting || (sel.inverted ? sel.to : sel.from);
      else shiftSelecting = null;
      // First give onKeyEvent option a chance to handle this.
      if (options.onKeyEvent && options.onKeyEvent(instance, addStop(e))) return;

      if (code == 33 || code == 34) {scrollPage(code == 34); return e_preventDefault(e);} // page up/down
      if (mod && ((code == 36 || code == 35) || // ctrl-home/end
                  mac && (code == 38 || code == 40))) { // cmd-up/down
        scrollEnd(code == 36 || code == 38); return e_preventDefault(e);
      }
      if (mod && code == 65) {selectAll(); return e_preventDefault(e);} // ctrl-a
      if (!options.readOnly) {
        if (!anyMod && code == 13) {return;} // enter
        if (!anyMod && code == 9 && handleTab(e.shiftKey)) return e_preventDefault(e); // tab
        if (mod && code == 90) {undo(); return e_preventDefault(e);} // ctrl-z
        if (mod && ((e.shiftKey && code == 90) || code == 89)) {redo(); return e_preventDefault(e);} // ctrl-shift-z, ctrl-y
      }
      if (code == 36) { if (options.smartHome) { smartHome(); return e_preventDefault(e); } }

      // Key id to use in the movementKeys map. We also pass it to
      // fastPoll in order to 'self learn'. We need this because
      // reducedSelection, the hack where we collapse the selection to
      // its start when it is inverted and a movement key is pressed
      // (and later restore it again), shouldn't be used for
      // non-movement keys.
      curKeyId = (mod ? "c" : "") + (e.altKey ? "a" : "") + code;
      if (sel.inverted && movementKeys[curKeyId] === true) {
        var range = selRange(input);
        if (range) {
          reducedSelection = {anchor: range.start};
          setSelRange(input, range.start, range.start);
        }
      }
      // Don't save the key as a movementkey unless it had a modifier
      if (!mod && !e.altKey) curKeyId = null;
      fastPoll(curKeyId);
    }
    function onKeyUp(e) {
      if (options.onKeyEvent && options.onKeyEvent(instance, addStop(e))) return;
      if (reducedSelection) {
        reducedSelection = null;
        updateInput = true;
      }
      if (e.keyCode == 16) shiftSelecting = null;
    }
    function onKeyPress(e) {
      if (options.onKeyEvent && options.onKeyEvent(instance, addStop(e))) return;
      if (options.electricChars && mode.electricChars) {
        var ch = String.fromCharCode(e.charCode == null ? e.keyCode : e.charCode);
        if (mode.electricChars.indexOf(ch) > -1)
          setTimeout(operation(function() {indentLine(sel.to.line, "smart");}), 50);
      }
      var code = e.keyCode;
      // Re-stop tab and enter. Necessary on some browsers.
      if (code == 13) {if (!options.readOnly) handleEnter(); e_preventDefault(e);}
      else if (!e.ctrlKey && !e.altKey && !e.metaKey && code == 9 && options.tabMode != "default") e_preventDefault(e);
      else fastPoll(curKeyId);
    }

    function onFocus() {
      if (options.readOnly == "nocursor") return;
      if (!focused) {
        if (options.onFocus) options.onFocus(instance);
        focused = true;
        if (wrapper.className.search(/\bCodeMirror-focused\b/) == -1)
          wrapper.className += " CodeMirror-focused";
        if (!leaveInputAlone) prepareInput();
      }
      slowPoll();
      restartBlink();
    }
    function onBlur() {
      if (focused) {
        if (options.onBlur) options.onBlur(instance);
        focused = false;
        wrapper.className = wrapper.className.replace(" CodeMirror-focused", "");
      }
      clearInterval(blinker);
      setTimeout(function() {if (!focused) shiftSelecting = null;}, 150);
    }

    // Replace the range from from to to by the strings in newText.
    // Afterwards, set the selection to selFrom, selTo.
    function updateLines(from, to, newText, selFrom, selTo) {
      if (history) {
        var old = [];
        for (var i = from.line, e = to.line + 1; i < e; ++i) old.push(lines[i].text);
        history.addChange(from.line, newText.length, old);
        while (history.done.length > options.undoDepth) history.done.shift();
      }
      updateLinesNoUndo(from, to, newText, selFrom, selTo);
    }
    function unredoHelper(from, to) {
      var change = from.pop();
      if (change) {
        var replaced = [], end = change.start + change.added;
        for (var i = change.start; i < end; ++i) replaced.push(lines[i].text);
        to.push({start: change.start, added: change.old.length, old: replaced});
        var pos = clipPos({line: change.start + change.old.length - 1,
                           ch: editEnd(replaced[replaced.length-1], change.old[change.old.length-1])});
        updateLinesNoUndo({line: change.start, ch: 0}, {line: end - 1, ch: lines[end-1].text.length}, change.old, pos, pos);
        updateInput = true;
      }
    }
    function undo() {unredoHelper(history.done, history.undone);}
    function redo() {unredoHelper(history.undone, history.done);}

    function updateLinesNoUndo(from, to, newText, selFrom, selTo) {
      var recomputeMaxLength = false, maxLineLength = maxLine.length;
      for (var i = from.line; i <= to.line; ++i) {
        if (lines[i].text.length == maxLineLength) {recomputeMaxLength = true; break;}
      }

      var nlines = to.line - from.line, firstLine = lines[from.line], lastLine = lines[to.line];
      // First adjust the line structure, taking some care to leave highlighting intact.
      if (firstLine == lastLine) {
        if (newText.length == 1)
          firstLine.replace(from.ch, to.ch, newText[0]);
        else {
          lastLine = firstLine.split(to.ch, newText[newText.length-1]);
          var spliceargs = [from.line + 1, nlines];
          firstLine.replace(from.ch, firstLine.text.length, newText[0]);
          for (var i = 1, e = newText.length - 1; i < e; ++i) spliceargs.push(new Line(newText[i]));
          spliceargs.push(lastLine);
          lines.splice.apply(lines, spliceargs);
        }
      }
      else if (newText.length == 1) {
        firstLine.replace(from.ch, firstLine.text.length, newText[0] + lastLine.text.slice(to.ch));
        lines.splice(from.line + 1, nlines);
      }
      else {
        var spliceargs = [from.line + 1, nlines - 1];
        firstLine.replace(from.ch, firstLine.text.length, newText[0]);
        lastLine.replace(0, to.ch, newText[newText.length-1]);
        for (var i = 1, e = newText.length - 1; i < e; ++i) spliceargs.push(new Line(newText[i]));
        lines.splice.apply(lines, spliceargs);
      }


      for (var i = from.line, e = i + newText.length; i < e; ++i) {
        var l = lines[i].text;
        if (l.length > maxLineLength) {
          maxLine = l; maxLineLength = l.length; maxWidth = null;
          recomputeMaxLength = false;
        }
      }
      if (recomputeMaxLength) {
        maxLineLength = 0; maxLine = ""; maxWidth = null;
        for (var i = 0, e = lines.length; i < e; ++i) {
          var l = lines[i].text;
          if (l.length > maxLineLength) {
            maxLineLength = l.length; maxLine = l;
          }
        }
      }

      // Add these lines to the work array, so that they will be
      // highlighted. Adjust work lines if lines were added/removed.
      var newWork = [], lendiff = newText.length - nlines - 1;
      for (var i = 0, l = work.length; i < l; ++i) {
        var task = work[i];
        if (task < from.line) newWork.push(task);
        else if (task > to.line) newWork.push(task + lendiff);
      }
      if (newText.length < 5) {
        highlightLines(from.line, from.line + newText.length);
        newWork.push(from.line + newText.length);
      } else {
        newWork.push(from.line);
      }
      work = newWork;
      startWorker(100);
      // Remember that these lines changed, for updating the display
      changes.push({from: from.line, to: to.line + 1, diff: lendiff});
      textChanged = {from: from, to: to, text: newText};

      // Update the selection
      function updateLine(n) {return n <= Math.min(to.line, to.line + lendiff) ? n : n + lendiff;}
      setSelection(selFrom, selTo, updateLine(sel.from.line), updateLine(sel.to.line));

      // Make sure the scroll-size div has the correct height.
      code.style.height = (lines.length * lineHeight() + 2 * paddingTop()) + "px";
    }

    function replaceRange(code, from, to) {
      from = clipPos(from);
      if (!to) to = from; else to = clipPos(to);
      code = splitLines(code);
      function adjustPos(pos) {
        if (posLess(pos, from)) return pos;
        if (!posLess(to, pos)) return end;
        var line = pos.line + code.length - (to.line - from.line) - 1;
        var ch = pos.ch;
        if (pos.line == to.line)
          ch += code[code.length-1].length - (to.ch - (to.line == from.line ? from.ch : 0));
        return {line: line, ch: ch};
      }
      var end;
      replaceRange1(code, from, to, function(end1) {
        end = end1;
        return {from: adjustPos(sel.from), to: adjustPos(sel.to)};
      });
      return end;
    }
    function replaceSelection(code, collapse) {
      replaceRange1(splitLines(code), sel.from, sel.to, function(end) {
        if (collapse == "end") return {from: end, to: end};
        else if (collapse == "start") return {from: sel.from, to: sel.from};
        else return {from: sel.from, to: end};
      });
    }
    function replaceRange1(code, from, to, computeSel) {
      var endch = code.length == 1 ? code[0].length + from.ch : code[code.length-1].length;
      var newSel = computeSel({line: from.line + code.length - 1, ch: endch});
      updateLines(from, to, code, newSel.from, newSel.to);
    }

    function getRange(from, to) {
      var l1 = from.line, l2 = to.line;
      if (l1 == l2) return lines[l1].text.slice(from.ch, to.ch);
      var code = [lines[l1].text.slice(from.ch)];
      for (var i = l1 + 1; i < l2; ++i) code.push(lines[i].text);
      code.push(lines[l2].text.slice(0, to.ch));
      return code.join("\n");
    }
    function getSelection() {
      return getRange(sel.from, sel.to);
    }

    var pollingFast = false; // Ensures slowPoll doesn't cancel fastPoll
    function slowPoll() {
      if (pollingFast) return;
      poll.set(2000, function() {
        startOperation();
        readInput();
        if (focused) slowPoll();
        endOperation();
      });
    }
    function fastPoll(keyId) {
      var missed = false;
      pollingFast = true;
      function p() {
        startOperation();
        var changed = readInput();
        if (changed && keyId) {
          if (changed == "moved" && movementKeys[keyId] == null) movementKeys[keyId] = true;
          if (changed == "changed") movementKeys[keyId] = false;
        }
        if (!changed && !missed) {missed = true; poll.set(80, p);}
        else {pollingFast = false; slowPoll();}
        endOperation();
      }
      poll.set(20, p);
    }

    // Inspects the textarea, compares its state (content, selection)
    // to the data in the editing variable, and updates the editor
    // content or cursor if something changed.
    function readInput() {
      if (leaveInputAlone || !focused) return;
      var changed = false, text = input.value, sr = selRange(input);
      if (!sr) return false;
      var changed = editing.text != text, rs = reducedSelection;
      var moved = changed || sr.start != editing.start || sr.end != (rs ? editing.start : editing.end);
      if (!moved && !rs) return false;
      if (changed) {
        shiftSelecting = reducedSelection = null;
        if (options.readOnly) {updateInput = true; return "changed";}
      }

      // Compute selection start and end based on start/end offsets in textarea
      function computeOffset(n, startLine) {
        var pos = 0;
        for (;;) {
          var found = text.indexOf("\n", pos);
          if (found == -1 || (text.charAt(found-1) == "\r" ? found - 1 : found) >= n)
            return {line: startLine, ch: n - pos};
          ++startLine;
          pos = found + 1;
        }
      }
      var from = computeOffset(sr.start, editing.from),
          to = computeOffset(sr.end, editing.from);
      // Here we have to take the reducedSelection hack into account,
      // so that you can, for example, press shift-up at the start of
      // your selection and have the right thing happen.
      if (rs) {
        var head = sr.start == rs.anchor ? to : from;
        var tail = shiftSelecting ? sel.to : sr.start == rs.anchor ? from : to;
        if (sel.inverted = posLess(head, tail)) { from = head; to = tail; }
        else { reducedSelection = null; from = tail; to = head; }
      }

      // In some cases (cursor on same line as before), we don't have
      // to update the textarea content at all.
      if (from.line == to.line && from.line == sel.from.line && from.line == sel.to.line && !shiftSelecting)
        updateInput = false;

      // Magic mess to extract precise edited range from the changed
      // string.
      if (changed) {
        var start = 0, end = text.length, len = Math.min(end, editing.text.length);
        var c, line = editing.from, nl = -1;
        while (start < len && (c = text.charAt(start)) == editing.text.charAt(start)) {
          ++start;
          if (c == "\n") {line++; nl = start;}
        }
        var ch = nl > -1 ? start - nl : start, endline = editing.to - 1, edend = editing.text.length;
        for (;;) {
          c = editing.text.charAt(edend);
          if (text.charAt(end) != c) {++end; ++edend; break;}
          if (c == "\n") endline--;
          if (edend <= start || end <= start) break;
          --end; --edend;
        }
        var nl = editing.text.lastIndexOf("\n", edend - 1), endch = nl == -1 ? edend : edend - nl - 1;
        updateLines({line: line, ch: ch}, {line: endline, ch: endch}, splitLines(text.slice(start, end)), from, to);
        if (line != endline || from.line != line) updateInput = true;
      }
      else setSelection(from, to);

      editing.text = text; editing.start = sr.start; editing.end = sr.end;
      return changed ? "changed" : moved ? "moved" : false;
    }

    // Set the textarea content and selection range to match the
    // editor state.
    function prepareInput() {
      var text = [];
      var from = Math.max(0, sel.from.line - 1), to = Math.min(lines.length, sel.to.line + 2);
      for (var i = from; i < to; ++i) text.push(lines[i].text);
      text = input.value = text.join(lineSep);
      var startch = sel.from.ch, endch = sel.to.ch;
      for (var i = from; i < sel.from.line; ++i)
        startch += lineSep.length + lines[i].text.length;
      for (var i = from; i < sel.to.line; ++i)
        endch += lineSep.length + lines[i].text.length;
      editing = {text: text, from: from, to: to, start: startch, end: endch};
      setSelRange(input, startch, reducedSelection ? startch : endch);
    }
    function focusInput() {
      if (options.readOnly != "nocursor") input.focus();
    }

    function scrollEditorIntoView() {
      if (!cursor.getBoundingClientRect) return;
      var rect = cursor.getBoundingClientRect();
      var winH = window.innerHeight || document.body.offsetHeight || document.documentElement.offsetHeight;
      if (rect.top < 0 || rect.bottom > winH) cursor.scrollIntoView();
    }
    function scrollCursorIntoView() {
      var cursor = localCoords(sel.inverted ? sel.from : sel.to);
      return scrollIntoView(cursor.x, cursor.y, cursor.x, cursor.yBot);
    }
    function scrollIntoView(x1, y1, x2, y2) {
      var pl = paddingLeft(), pt = paddingTop(), lh = lineHeight();
      y1 += pt; y2 += pt; x1 += pl; x2 += pl;
      var screen = scroller.clientHeight, screentop = scroller.scrollTop, scrolled = false, result = true;
      if (y1 < screentop) {scroller.scrollTop = Math.max(0, y1 - 2*lh); scrolled = true;}
      else if (y2 > screentop + screen) {scroller.scrollTop = y2 + lh - screen; scrolled = true;}

      var screenw = scroller.clientWidth, screenleft = scroller.scrollLeft;
      if (x1 < screenleft) {
        if (x1 < 50) x1 = 0;
        scroller.scrollLeft = Math.max(0, x1 - 10);
        scrolled = true;
      }
      else if (x2 > screenw + screenleft) {
        scroller.scrollLeft = x2 + 10 - screenw;
        scrolled = true;
        if (x2 > code.clientWidth) result = false;
      }
      if (scrolled && options.onScroll) options.onScroll(instance);
      return result;
    }

    function visibleLines() {
      var lh = lineHeight(), top = scroller.scrollTop - paddingTop();
      return {from: Math.min(lines.length, Math.max(0, Math.floor(top / lh))),
              to: Math.min(lines.length, Math.ceil((top + scroller.clientHeight) / lh))};
    }
    // Uses a set of changes plus the current scroll position to
    // determine which DOM updates have to be made, and makes the
    // updates.
    function updateDisplay(changes) {
      if (!scroller.clientWidth) {
        showingFrom = showingTo = 0;
        return;
      }
      // First create a range of theoretically intact lines, and punch
      // holes in that using the change info.
      var intact = changes === true ? [] : [{from: showingFrom, to: showingTo, domStart: 0}];
      for (var i = 0, l = changes.length || 0; i < l; ++i) {
        var change = changes[i], intact2 = [], diff = change.diff || 0;
        for (var j = 0, l2 = intact.length; j < l2; ++j) {
          var range = intact[j];
          if (change.to <= range.from)
            intact2.push({from: range.from + diff, to: range.to + diff, domStart: range.domStart});
          else if (range.to <= change.from)
            intact2.push(range);
          else {
            if (change.from > range.from)
              intact2.push({from: range.from, to: change.from, domStart: range.domStart})
            if (change.to < range.to)
              intact2.push({from: change.to + diff, to: range.to + diff,
                            domStart: range.domStart + (change.to - range.from)});
          }
        }
        intact = intact2;
      }

      // Then, determine which lines we'd want to see, and which
      // updates have to be made to get there.
      var visible = visibleLines();
      var from = Math.min(showingFrom, Math.max(visible.from - 3, 0)),
          to = Math.min(lines.length, Math.max(showingTo, visible.to + 3)),
          updates = [], domPos = 0, domEnd = showingTo - showingFrom, pos = from, changedLines = 0;

      for (var i = 0, l = intact.length; i < l; ++i) {
        var range = intact[i];
        if (range.to <= from) continue;
        if (range.from >= to) break;
        if (range.domStart > domPos || range.from > pos) {
          updates.push({from: pos, to: range.from, domSize: range.domStart - domPos, domStart: domPos});
          changedLines += range.from - pos;
        }
        pos = range.to;
        domPos = range.domStart + (range.to - range.from);
      }
      if (domPos != domEnd || pos != to) {
        changedLines += Math.abs(to - pos);
        updates.push({from: pos, to: to, domSize: domEnd - domPos, domStart: domPos});
      }

      if (!updates.length) return;
      lineDiv.style.display = "none";
      // If more than 30% of the screen needs update, just do a full
      // redraw (which is quicker than patching)
      if (changedLines > (visible.to - visible.from) * .3)
        refreshDisplay(from = Math.max(visible.from - 10, 0), to = Math.min(visible.to + 7, lines.length));
      // Otherwise, only update the stuff that needs updating.
      else
        patchDisplay(updates);
      lineDiv.style.display = "";

      // Position the mover div to align with the lines it's supposed
      // to be showing (which will cover the visible display)
      var different = from != showingFrom || to != showingTo || lastHeight != scroller.clientHeight;
      showingFrom = from; showingTo = to;
      mover.style.top = (from * lineHeight()) + "px";
      if (different) {
        lastHeight = scroller.clientHeight;
        code.style.height = (lines.length * lineHeight() + 2 * paddingTop()) + "px";
      }
      if (different || updates.length) updateGutter();

      if (maxWidth == null) maxWidth = stringWidth(maxLine);
      if (maxWidth > scroller.clientWidth) {
        lineSpace.style.width = maxWidth + "px";
        // Needed to prevent odd wrapping/hiding of widgets placed in here.
        code.style.width = "";
        code.style.width = scroller.scrollWidth + "px";
      } else {
        lineSpace.style.width = code.style.width = "";
      }

      // Since this is all rather error prone, it is honoured with the
      // only assertion in the whole file.
      if (lineDiv.childNodes.length != showingTo - showingFrom)
        throw new Error("BAD PATCH! " + JSON.stringify(updates) + " size=" + (showingTo - showingFrom) +
                        " nodes=" + lineDiv.childNodes.length);
      updateCursor();
    }

    function refreshDisplay(from, to) {
      var html = [], start = {line: from, ch: 0}, inSel = posLess(sel.from, start) && !posLess(sel.to, start);
      for (var i = from; i < to; ++i) {
        var ch1 = null, ch2 = null;
        if (inSel) {
          ch1 = 0;
          if (sel.to.line == i) {inSel = false; ch2 = sel.to.ch;}
        }
        else if (sel.from.line == i) {
          if (sel.to.line == i) {ch1 = sel.from.ch; ch2 = sel.to.ch;}
          else {inSel = true; ch1 = sel.from.ch;}
        }
        html.push(lines[i].getHTML(ch1, ch2, true));
      }
      lineDiv.innerHTML = html.join("");
    }
    function patchDisplay(updates) {
      // Slightly different algorithm for IE (badInnerHTML), since
      // there .innerHTML on PRE nodes is dumb, and discards
      // whitespace.
      var sfrom = sel.from.line, sto = sel.to.line, off = 0,
          scratch = badInnerHTML && targetDocument.createElement("div");
      for (var i = 0, e = updates.length; i < e; ++i) {
        var rec = updates[i];
        var extra = (rec.to - rec.from) - rec.domSize;
        var nodeAfter = lineDiv.childNodes[rec.domStart + rec.domSize + off] || null;
        if (badInnerHTML)
          for (var j = Math.max(-extra, rec.domSize); j > 0; --j)
            lineDiv.removeChild(nodeAfter ? nodeAfter.previousSibling : lineDiv.lastChild);
        else if (extra) {
          for (var j = Math.max(0, extra); j > 0; --j)
            lineDiv.insertBefore(targetDocument.createElement("pre"), nodeAfter);
          for (var j = Math.max(0, -extra); j > 0; --j)
            lineDiv.removeChild(nodeAfter ? nodeAfter.previousSibling : lineDiv.lastChild);
        }
        var node = lineDiv.childNodes[rec.domStart + off], inSel = sfrom < rec.from && sto >= rec.from;
        for (var j = rec.from; j < rec.to; ++j) {
          var ch1 = null, ch2 = null;
          if (inSel) {
            ch1 = 0;
            if (sto == j) {inSel = false; ch2 = sel.to.ch;}
          }
          else if (sfrom == j) {
            if (sto == j) {ch1 = sel.from.ch; ch2 = sel.to.ch;}
            else {inSel = true; ch1 = sel.from.ch;}
          }
          if (badInnerHTML) {
            scratch.innerHTML = lines[j].getHTML(ch1, ch2, true);
            lineDiv.insertBefore(scratch.firstChild, nodeAfter);
          }
          else {
            node.innerHTML = lines[j].getHTML(ch1, ch2, false);
            node.className = lines[j].className || "";
            node = node.nextSibling;
          }
        }
        off += extra;
      }
    }

    function updateGutter() {
      if (!options.gutter && !options.lineNumbers) return;
      var hText = mover.offsetHeight, hEditor = scroller.clientHeight;
      gutter.style.height = (hText - hEditor < 2 ? hEditor : hText) + "px";
      var html = [];
      for (var i = showingFrom; i < Math.max(showingTo, showingFrom + 1); ++i) {
        var marker = lines[i].gutterMarker;
        var text = options.lineNumbers ? i + options.firstLineNumber : null;
        if (marker && marker.text)
          text = marker.text.replace("%N%", text != null ? text : "");
        else if (text == null)
          text = "\u00a0";
        html.push((marker && marker.style ? '<pre class="' + marker.style + '">' : "<pre>"), text, "</pre>");
      }
      gutter.style.display = "none";
      gutterText.innerHTML = html.join("");
      var minwidth = String(lines.length).length, firstNode = gutterText.firstChild, val = eltText(firstNode), pad = "";
      while (val.length + pad.length < minwidth) pad += "\u00a0";
      if (pad) firstNode.insertBefore(targetDocument.createTextNode(pad), firstNode.firstChild);
      gutter.style.display = "";
      lineSpace.style.marginLeft = gutter.offsetWidth + "px";
    }
    function updateCursor() {
      var head = sel.inverted ? sel.from : sel.to, lh = lineHeight();
      var x = charX(head.line, head.ch);
      inputDiv.style.top = (head.line * lh - scroller.scrollTop) + "px";
      inputDiv.style.left = (x - scroller.scrollLeft) + "px";
      if (posEq(sel.from, sel.to)) {
        cursor.style.top = (head.line - showingFrom) * lh + "px";
        cursor.style.left = x + "px";
        cursor.style.display = "";
      }
      else cursor.style.display = "none";
    }

    function setSelectionUser(from, to) {
      var sh = shiftSelecting && clipPos(shiftSelecting);
      if (sh) {
        if (posLess(sh, from)) from = sh;
        else if (posLess(to, sh)) to = sh;
      }
      setSelection(from, to);
    }
    // Update the selection. Last two args are only used by
    // updateLines, since they have to be expressed in the line
    // numbers before the update.
    function setSelection(from, to, oldFrom, oldTo) {
      if (posEq(sel.from, from) && posEq(sel.to, to)) return;
      if (posLess(to, from)) {var tmp = to; to = from; from = tmp;}

      if (posEq(from, to)) sel.inverted = false;
      else if (posEq(from, sel.to)) sel.inverted = false;
      else if (posEq(to, sel.from)) sel.inverted = true;

      // Some ugly logic used to only mark the lines that actually did
      // see a change in selection as changed, rather than the whole
      // selected range.
      if (oldFrom == null) {oldFrom = sel.from.line; oldTo = sel.to.line;}
      if (posEq(from, to)) {
        if (!posEq(sel.from, sel.to))
          changes.push({from: oldFrom, to: oldTo + 1});
      }
      else if (posEq(sel.from, sel.to)) {
        changes.push({from: from.line, to: to.line + 1});
      }
      else {
        if (!posEq(from, sel.from)) {
          if (from.line < oldFrom)
            changes.push({from: from.line, to: Math.min(to.line, oldFrom) + 1});
          else
            changes.push({from: oldFrom, to: Math.min(oldTo, from.line) + 1});
        }
        if (!posEq(to, sel.to)) {
          if (to.line < oldTo)
            changes.push({from: Math.max(oldFrom, from.line), to: oldTo + 1});
          else
            changes.push({from: Math.max(from.line, oldTo), to: to.line + 1});
        }
      }
      sel.from = from; sel.to = to;
      selectionChanged = true;
    }
    function setCursor(line, ch, user) {
      var pos = clipPos({line: line, ch: ch || 0});
      (user ? setSelectionUser : setSelection)(pos, pos);
    }

    function clipLine(n) {return Math.max(0, Math.min(n, lines.length-1));}
    function clipPos(pos) {
      if (pos.line < 0) return {line: 0, ch: 0};
      if (pos.line >= lines.length) return {line: lines.length-1, ch: lines[lines.length-1].text.length};
      var ch = pos.ch, linelen = lines[pos.line].text.length;
      if (ch == null || ch > linelen) return {line: pos.line, ch: linelen};
      else if (ch < 0) return {line: pos.line, ch: 0};
      else return pos;
    }

    function scrollPage(down) {
      var linesPerPage = Math.floor(scroller.clientHeight / lineHeight()), head = sel.inverted ? sel.from : sel.to;
      setCursor(head.line + (Math.max(linesPerPage - 1, 1) * (down ? 1 : -1)), head.ch, true);
    }
    function scrollEnd(top) {
      var pos = top ? {line: 0, ch: 0} : {line: lines.length - 1, ch: lines[lines.length-1].text.length};
      setSelectionUser(pos, pos);
    }
    function selectAll() {
      var endLine = lines.length - 1;
      setSelection({line: 0, ch: 0}, {line: endLine, ch: lines[endLine].text.length});
    }
    function selectWordAt(pos) {
      var line = lines[pos.line].text;
      var start = pos.ch, end = pos.ch;
      while (start > 0 && /\w/.test(line.charAt(start - 1))) --start;
      while (end < line.length && /\w/.test(line.charAt(end))) ++end;
      setSelectionUser({line: pos.line, ch: start}, {line: pos.line, ch: end});
    }
    function selectLine(line) {
      setSelectionUser({line: line, ch: 0}, {line: line, ch: lines[line].text.length});
    }
    function handleEnter() {
      replaceSelection("\n", "end");
      if (options.enterMode != "flat")
        indentLine(sel.from.line, options.enterMode == "keep" ? "prev" : "smart");
    }
    function handleTab(shift) {
      function indentSelected(mode) {
        if (posEq(sel.from, sel.to)) return indentLine(sel.from.line, mode);
        var e = sel.to.line - (sel.to.ch ? 0 : 1);
        for (var i = sel.from.line; i <= e; ++i) indentLine(i, mode);
      }
      shiftSelecting = null;
      switch (options.tabMode) {
      case "default":
        return false;
      case "indent":
        indentSelected("smart");
        break;
      case "classic":
        if (posEq(sel.from, sel.to)) {
          if (shift) indentLine(sel.from.line, "smart");
          else replaceSelection("\t", "end");
          break;
        }
      case "shift":
        indentSelected(shift ? "subtract" : "add");
        break;
      }
      return true;
    }
    function smartHome() {
      var firstNonWS = Math.max(0, lines[sel.from.line].text.search(/\S/));
      setCursor(sel.from.line, sel.from.ch <= firstNonWS && sel.from.ch ? 0 : firstNonWS, true);
    }

    function indentLine(n, how) {
      if (how == "smart") {
        if (!mode.indent) how = "prev";
        else var state = getStateBefore(n);
      }

      var line = lines[n], curSpace = line.indentation(), curSpaceString = line.text.match(/^\s*/)[0], indentation;
      if (how == "prev") {
        if (n) indentation = lines[n-1].indentation();
        else indentation = 0;
      }
      else if (how == "smart") indentation = mode.indent(state, line.text.slice(curSpaceString.length));
      else if (how == "add") indentation = curSpace + options.indentUnit;
      else if (how == "subtract") indentation = curSpace - options.indentUnit;
      indentation = Math.max(0, indentation);
      var diff = indentation - curSpace;

      if (!diff) {
        if (sel.from.line != n && sel.to.line != n) return;
        var indentString = curSpaceString;
      }
      else {
        var indentString = "", pos = 0;
        if (options.indentWithTabs)
          for (var i = Math.floor(indentation / tabSize); i; --i) {pos += tabSize; indentString += "\t";}
        while (pos < indentation) {++pos; indentString += " ";}
      }

      replaceRange(indentString, {line: n, ch: 0}, {line: n, ch: curSpaceString.length});
    }

    function loadMode() {
      mode = CodeMirror.getMode(options, options.mode);
      for (var i = 0, l = lines.length; i < l; ++i)
        lines[i].stateAfter = null;
      work = [0];
      startWorker();
    }
    function gutterChanged() {
      var visible = options.gutter || options.lineNumbers;
      gutter.style.display = visible ? "" : "none";
      if (visible) updateGutter();
      else lineDiv.parentNode.style.marginLeft = 0;
    }

    function markText(from, to, className) {
      from = clipPos(from); to = clipPos(to);
      var accum = [];
      function add(line, from, to, className) {
        var line = lines[line], mark = line.addMark(from, to, className);
        mark.line = line;
        accum.push(mark);
      }
      if (from.line == to.line) add(from.line, from.ch, to.ch, className);
      else {
        add(from.line, from.ch, null, className);
        for (var i = from.line + 1, e = to.line; i < e; ++i)
          add(i, 0, null, className);
        add(to.line, 0, to.ch, className);
      }
      changes.push({from: from.line, to: to.line + 1});
      return function() {
        var start, end;
        for (var i = 0; i < accum.length; ++i) {
          var mark = accum[i], found = indexOf(lines, mark.line);
          mark.line.removeMark(mark);
          if (found > -1) {
            if (start == null) start = found;
            end = found;
          }
        }
        if (start != null) changes.push({from: start, to: end + 1});
      };
    }

    function addGutterMarker(line, text, className) {
      if (typeof line == "number") line = lines[clipLine(line)];
      line.gutterMarker = {text: text, style: className};
      updateGutter();
      return line;
    }
    function removeGutterMarker(line) {
      if (typeof line == "number") line = lines[clipLine(line)];
      line.gutterMarker = null;
      updateGutter();
    }
    function setLineClass(line, className) {
      if (typeof line == "number") {
        var no = line;
        line = lines[clipLine(line)];
      }
      else {
        var no = indexOf(lines, line);
        if (no == -1) return null;
      }
      if (line.className != className) {
        line.className = className;
        changes.push({from: no, to: no + 1});
      }
      return line;
    }

    function lineInfo(line) {
      if (typeof line == "number") {
        var n = line;
        line = lines[line];
        if (!line) return null;
      }
      else {
        var n = indexOf(lines, line);
        if (n == -1) return null;
      }
      var marker = line.gutterMarker;
      return {line: n, text: line.text, markerText: marker && marker.text, markerClass: marker && marker.style};
    }

    function stringWidth(str) {
      measure.innerHTML = "<pre><span>x</span></pre>";
      measure.firstChild.firstChild.firstChild.nodeValue = str;
      return measure.firstChild.firstChild.offsetWidth || 10;
    }
    // These are used to go from pixel positions to character
    // positions, taking varying character widths into account.
    function charX(line, pos) {
      if (pos == 0) return 0;
      measure.innerHTML = "<pre><span>" + lines[line].getHTML(null, null, false, pos) + "</span></pre>";
      return measure.firstChild.firstChild.offsetWidth;
    }
    function charFromX(line, x) {
      if (x <= 0) return 0;
      var lineObj = lines[line], text = lineObj.text;
      function getX(len) {
        measure.innerHTML = "<pre><span>" + lineObj.getHTML(null, null, false, len) + "</span></pre>";
        return measure.firstChild.firstChild.offsetWidth;
      }
      var from = 0, fromX = 0, to = text.length, toX;
      // Guess a suitable upper bound for our search.
      var estimated = Math.min(to, Math.ceil(x / stringWidth("x")));
      for (;;) {
        var estX = getX(estimated);
        if (estX <= x && estimated < to) estimated = Math.min(to, Math.ceil(estimated * 1.2));
        else {toX = estX; to = estimated; break;}
      }
      if (x > toX) return to;
      // Try to guess a suitable lower bound as well.
      estimated = Math.floor(to * 0.8); estX = getX(estimated);
      if (estX < x) {from = estimated; fromX = estX;}
      // Do a binary search between these bounds.
      for (;;) {
        if (to - from <= 1) return (toX - x > x - fromX) ? from : to;
        var middle = Math.ceil((from + to) / 2), middleX = getX(middle);
        if (middleX > x) {to = middle; toX = middleX;}
        else {from = middle; fromX = middleX;}
      }
    }

    function localCoords(pos, inLineWrap) {
      var lh = lineHeight(), line = pos.line - (inLineWrap ? showingFrom : 0);
      return {x: charX(pos.line, pos.ch), y: line * lh, yBot: (line + 1) * lh};
    }
    function pageCoords(pos) {
      var local = localCoords(pos, true), off = eltOffset(lineSpace);
      return {x: off.left + local.x, y: off.top + local.y, yBot: off.top + local.yBot};
    }

    function lineHeight() {
      var nlines = lineDiv.childNodes.length;
      if (nlines) return (lineDiv.offsetHeight / nlines) || 1;
      measure.innerHTML = "<pre>x</pre>";
      return measure.firstChild.offsetHeight || 1;
    }
    function paddingTop() {return lineSpace.offsetTop;}
    function paddingLeft() {return lineSpace.offsetLeft;}

    function posFromMouse(e, liberal) {
      var offW = eltOffset(scroller, true), x, y;
      // Fails unpredictably on IE[67] when mouse is dragged around quickly.
      try { x = e.clientX; y = e.clientY; } catch (e) { return null; }
      // This is a mess of a heuristic to try and determine whether a
      // scroll-bar was clicked or not, and to return null if one was
      // (and !liberal).
      if (!liberal && (x - offW.left > scroller.clientWidth || y - offW.top > scroller.clientHeight))
        return null;
      var offL = eltOffset(lineSpace, true);
      var line = showingFrom + Math.floor((y - offL.top) / lineHeight());
      return clipPos({line: line, ch: charFromX(clipLine(line), x - offL.left)});
    }
    function onContextMenu(e) {
      var pos = posFromMouse(e);
      if (!pos || window.opera) return; // Opera is difficult.
      if (posEq(sel.from, sel.to) || posLess(pos, sel.from) || !posLess(pos, sel.to))
        operation(setCursor)(pos.line, pos.ch);

      var oldCSS = input.style.cssText;
      inputDiv.style.position = "absolute";
      input.style.cssText = "position: fixed; width: 30px; height: 30px; top: " + (e.clientY - 5) +
        "px; left: " + (e.clientX - 5) + "px; z-index: 1000; background: white; " +
        "border-width: 0; outline: none; overflow: hidden; opacity: .05; filter: alpha(opacity=5);";
      leaveInputAlone = true;
      var val = input.value = getSelection();
      focusInput();
      setSelRange(input, 0, input.value.length);
      function rehide() {
        var newVal = splitLines(input.value).join("\n");
        if (newVal != val) operation(replaceSelection)(newVal, "end");
        inputDiv.style.position = "relative";
        input.style.cssText = oldCSS;
        leaveInputAlone = false;
        prepareInput();
        slowPoll();
      }
      
      if (gecko) {
        e_stop(e);
        var mouseup = connect(window, "mouseup", function() {
          mouseup();
          setTimeout(rehide, 20);
        }, true);
      }
      else {
        setTimeout(rehide, 50);
      }
    }

    // Cursor-blinking
    function restartBlink() {
      clearInterval(blinker);
      var on = true;
      cursor.style.visibility = "";
      blinker = setInterval(function() {
        cursor.style.visibility = (on = !on) ? "" : "hidden";
      }, 650);
    }

    var matching = {"(": ")>", ")": "(<", "[": "]>", "]": "[<", "{": "}>", "}": "{<"};
    function matchBrackets(autoclear) {
      var head = sel.inverted ? sel.from : sel.to, line = lines[head.line], pos = head.ch - 1;
      var match = (pos >= 0 && matching[line.text.charAt(pos)]) || matching[line.text.charAt(++pos)];
      if (!match) return;
      var ch = match.charAt(0), forward = match.charAt(1) == ">", d = forward ? 1 : -1, st = line.styles;
      for (var off = pos + 1, i = 0, e = st.length; i < e; i+=2)
        if ((off -= st[i].length) <= 0) {var style = st[i+1]; break;}

      var stack = [line.text.charAt(pos)], re = /[(){}[\]]/;
      function scan(line, from, to) {
        if (!line.text) return;
        var st = line.styles, pos = forward ? 0 : line.text.length - 1, cur;
        for (var i = forward ? 0 : st.length - 2, e = forward ? st.length : -2; i != e; i += 2*d) {
          var text = st[i];
          if (st[i+1] != null && st[i+1] != style) {pos += d * text.length; continue;}
          for (var j = forward ? 0 : text.length - 1, te = forward ? text.length : -1; j != te; j += d, pos+=d) {
            if (pos >= from && pos < to && re.test(cur = text.charAt(j))) {
              var match = matching[cur];
              if (match.charAt(1) == ">" == forward) stack.push(cur);
              else if (stack.pop() != match.charAt(0)) return {pos: pos, match: false};
              else if (!stack.length) return {pos: pos, match: true};
            }
          }
        }
      }
      for (var i = head.line, e = forward ? Math.min(i + 100, lines.length) : Math.max(-1, i - 100); i != e; i+=d) {
        var line = lines[i], first = i == head.line;
        var found = scan(line, first && forward ? pos + 1 : 0, first && !forward ? pos : line.text.length);
        if (found) break;
      }
      if (!found) found = {pos: null, match: false};
      var style = found.match ? "CodeMirror-matchingbracket" : "CodeMirror-nonmatchingbracket";
      var one = markText({line: head.line, ch: pos}, {line: head.line, ch: pos+1}, style),
          two = found.pos != null
            ? markText({line: i, ch: found.pos}, {line: i, ch: found.pos + 1}, style)
            : function() {};
      var clear = operation(function(){one(); two();});
      if (autoclear) setTimeout(clear, 800);
      else bracketHighlighted = clear;
    }

    // Finds the line to start with when starting a parse. Tries to
    // find a line with a stateAfter, so that it can start with a
    // valid state. If that fails, it returns the line with the
    // smallest indentation, which tends to need the least context to
    // parse correctly.
    function findStartLine(n) {
      var minindent, minline;
      for (var search = n, lim = n - 40; search > lim; --search) {
        if (search == 0) return 0;
        var line = lines[search-1];
        if (line.stateAfter) return search;
        var indented = line.indentation();
        if (minline == null || minindent > indented) {
          minline = search - 1;
          minindent = indented;
        }
      }
      return minline;
    }
    function getStateBefore(n) {
      var start = findStartLine(n), state = start && lines[start-1].stateAfter;
      if (!state) state = startState(mode);
      else state = copyState(mode, state);
      for (var i = start; i < n; ++i) {
        var line = lines[i];
        line.highlight(mode, state);
        line.stateAfter = copyState(mode, state);
      }
      if (n < lines.length && !lines[n].stateAfter) work.push(n);
      return state;
    }
    function highlightLines(start, end) {
      var state = getStateBefore(start);
      for (var i = start; i < end; ++i) {
        var line = lines[i];
        line.highlight(mode, state);
        line.stateAfter = copyState(mode, state);
      }
    }
    function highlightWorker() {
      var end = +new Date + options.workTime;
      var foundWork = work.length;
      while (work.length) {
        if (!lines[showingFrom].stateAfter) var task = showingFrom;
        else var task = work.pop();
        if (task >= lines.length) continue;
        var start = findStartLine(task), state = start && lines[start-1].stateAfter;
        if (state) state = copyState(mode, state);
        else state = startState(mode);

        var unchanged = 0, compare = mode.compareStates, realChange = false;
        for (var i = start, l = lines.length; i < l; ++i) {
          var line = lines[i], hadState = line.stateAfter;
          if (+new Date > end) {
            work.push(i);
            startWorker(options.workDelay);
            if (realChange) changes.push({from: task, to: i + 1});
            return;
          }
          var changed = line.highlight(mode, state);
          if (changed) realChange = true;
          line.stateAfter = copyState(mode, state);
          if (compare) {
            if (hadState && compare(hadState, state)) break;
          } else {
            if (changed !== false || !hadState) unchanged = 0;
            else if (++unchanged > 3) break;
          }
        }
        if (realChange) changes.push({from: task, to: i + 1});
      }
      if (foundWork && options.onHighlightComplete)
        options.onHighlightComplete(instance);
    }
    function startWorker(time) {
      if (!work.length) return;
      highlight.set(time, operation(highlightWorker));
    }

    // Operations are used to wrap changes in such a way that each
    // change won't have to update the cursor and display (which would
    // be awkward, slow, and error-prone), but instead updates are
    // batched and then all combined and executed at once.
    function startOperation() {
      updateInput = null; changes = []; textChanged = selectionChanged = false;
    }
    function endOperation() {
      var reScroll = false;
      if (selectionChanged) reScroll = !scrollCursorIntoView();
      if (changes.length) updateDisplay(changes);
      else if (selectionChanged) updateCursor();
      if (reScroll) scrollCursorIntoView();
      if (selectionChanged) {scrollEditorIntoView(); restartBlink();}

      // updateInput can be set to a boolean value to force/prevent an
      // update.
      if (focused && !leaveInputAlone &&
          (updateInput === true || (updateInput !== false && selectionChanged)))
        prepareInput();

      if (selectionChanged && options.matchBrackets)
        setTimeout(operation(function() {
          if (bracketHighlighted) {bracketHighlighted(); bracketHighlighted = null;}
          matchBrackets(false);
        }), 20);
      var tc = textChanged; // textChanged can be reset by cursoractivity callback
      if (selectionChanged && options.onCursorActivity)
        options.onCursorActivity(instance);
      if (tc && options.onChange && instance)
        options.onChange(instance, tc);
    }
    var nestedOperation = 0;
    function operation(f) {
      return function() {
        if (!nestedOperation++) startOperation();
        try {var result = f.apply(this, arguments);}
        finally {if (!--nestedOperation) endOperation();}
        return result;
      };
    }

    function SearchCursor(query, pos, caseFold) {
      this.atOccurrence = false;
      if (caseFold == null) caseFold = typeof query == "string" && query == query.toLowerCase();

      if (pos && typeof pos == "object") pos = clipPos(pos);
      else pos = {line: 0, ch: 0};
      this.pos = {from: pos, to: pos};

      // The matches method is filled in based on the type of query.
      // It takes a position and a direction, and returns an object
      // describing the next occurrence of the query, or null if no
      // more matches were found.
      if (typeof query != "string") // Regexp match
        this.matches = function(reverse, pos) {
          if (reverse) {
            var line = lines[pos.line].text.slice(0, pos.ch), match = line.match(query), start = 0;
            while (match) {
              var ind = line.indexOf(match[0]);
              start += ind;
              line = line.slice(ind + 1);
              var newmatch = line.match(query);
              if (newmatch) match = newmatch;
              else break;
              start++;
            }
          }
          else {
            var line = lines[pos.line].text.slice(pos.ch), match = line.match(query),
                start = match && pos.ch + line.indexOf(match[0]);
          }
          if (match)
            return {from: {line: pos.line, ch: start},
                    to: {line: pos.line, ch: start + match[0].length},
                    match: match};
        };
      else { // String query
        if (caseFold) query = query.toLowerCase();
        var fold = caseFold ? function(str){return str.toLowerCase();} : function(str){return str;};
        var target = query.split("\n");
        // Different methods for single-line and multi-line queries
        if (target.length == 1)
          this.matches = function(reverse, pos) {
            var line = fold(lines[pos.line].text), len = query.length, match;
            if (reverse ? (pos.ch >= len && (match = line.lastIndexOf(query, pos.ch - len)) != -1)
                        : (match = line.indexOf(query, pos.ch)) != -1)
              return {from: {line: pos.line, ch: match},
                      to: {line: pos.line, ch: match + len}};
          };
        else
          this.matches = function(reverse, pos) {
            var ln = pos.line, idx = (reverse ? target.length - 1 : 0), match = target[idx], line = fold(lines[ln].text);
            var offsetA = (reverse ? line.indexOf(match) + match.length : line.lastIndexOf(match));
            if (reverse ? offsetA >= pos.ch || offsetA != match.length
                        : offsetA <= pos.ch || offsetA != line.length - match.length)
              return;
            for (;;) {
              if (reverse ? !ln : ln == lines.length - 1) return;
              line = fold(lines[ln += reverse ? -1 : 1].text);
              match = target[reverse ? --idx : ++idx];
              if (idx > 0 && idx < target.length - 1) {
                if (line != match) return;
                else continue;
              }
              var offsetB = (reverse ? line.lastIndexOf(match) : line.indexOf(match) + match.length);
              if (reverse ? offsetB != line.length - match.length : offsetB != match.length)
                return;
              var start = {line: pos.line, ch: offsetA}, end = {line: ln, ch: offsetB};
              return {from: reverse ? end : start, to: reverse ? start : end};
            }
          };
      }
    }

    SearchCursor.prototype = {
      findNext: function() {return this.find(false);},
      findPrevious: function() {return this.find(true);},

      find: function(reverse) {
        var self = this, pos = clipPos(reverse ? this.pos.from : this.pos.to);
        function savePosAndFail(line) {
          var pos = {line: line, ch: 0};
          self.pos = {from: pos, to: pos};
          self.atOccurrence = false;
          return false;
        }

        for (;;) {
          if (this.pos = this.matches(reverse, pos)) {
            this.atOccurrence = true;
            return this.pos.match || true;
          }
          if (reverse) {
            if (!pos.line) return savePosAndFail(0);
            pos = {line: pos.line-1, ch: lines[pos.line-1].text.length};
          }
          else {
            if (pos.line == lines.length - 1) return savePosAndFail(lines.length);
            pos = {line: pos.line+1, ch: 0};
          }
        }
      },

      from: function() {if (this.atOccurrence) return copyPos(this.pos.from);},
      to: function() {if (this.atOccurrence) return copyPos(this.pos.to);},

      replace: function(newText) {
        var self = this;
        if (this.atOccurrence)
          operation(function() {
            self.pos.to = replaceRange(newText, self.pos.from, self.pos.to);
          })();
      }
    };

    for (var ext in extensions)
      if (extensions.propertyIsEnumerable(ext) &&
          !instance.propertyIsEnumerable(ext))
        instance[ext] = extensions[ext];
    return instance;
  } // (end of function CodeMirror)

  // The default configuration options.
  CodeMirror.defaults = {
    value: "",
    mode: null,
    theme: "default",
    indentUnit: 2,
    indentWithTabs: false,
    tabMode: "classic",
    enterMode: "indent",
    electricChars: true,
    onKeyEvent: null,
    lineNumbers: false,
    gutter: false,
    firstLineNumber: 1,
    readOnly: false,
    smartHome: true,
    onChange: null,
    onCursorActivity: null,
    onGutterClick: null,
    onHighlightComplete: null,
    onFocus: null, onBlur: null, onScroll: null,
    matchBrackets: false,
    workTime: 100,
    workDelay: 200,
    undoDepth: 40,
    tabindex: null,
    document: window.document
  };

  // Known modes, by name and by MIME
  var modes = {}, mimeModes = {};
  CodeMirror.defineMode = function(name, mode) {
    if (!CodeMirror.defaults.mode && name != "null") CodeMirror.defaults.mode = name;
    modes[name] = mode;
  };
  CodeMirror.defineMIME = function(mime, spec) {
    mimeModes[mime] = spec;
  };
  CodeMirror.getMode = function(options, spec) {
    if (typeof spec == "string" && mimeModes.hasOwnProperty(spec))
      spec = mimeModes[spec];
    if (typeof spec == "string")
      var mname = spec, config = {};
    else if (spec != null)
      var mname = spec.name, config = spec;
    var mfactory = modes[mname];
    if (!mfactory) {
      if (window.console) console.warn("No mode " + mname + " found, falling back to plain text.");
      return CodeMirror.getMode(options, "text/plain");
    }
    return mfactory(options, config || {});
  };
  CodeMirror.listModes = function() {
    var list = [];
    for (var m in modes)
      if (modes.propertyIsEnumerable(m)) list.push(m);
    return list;
  };
  CodeMirror.listMIMEs = function() {
    var list = [];
    for (var m in mimeModes)
      if (mimeModes.propertyIsEnumerable(m)) list.push(m);
    return list;
  };

  var extensions = {};
  CodeMirror.defineExtension = function(name, func) {
    extensions[name] = func;
  };

  CodeMirror.fromTextArea = function(textarea, options) {
    if (!options) options = {};
    options.value = textarea.value;
    if (!options.tabindex && textarea.tabindex)
      options.tabindex = textarea.tabindex;

    function save() {textarea.value = instance.getValue();}
    if (textarea.form) {
      // Deplorable hack to make the submit method do the right thing.
      var rmSubmit = connect(textarea.form, "submit", save, true);
      if (typeof textarea.form.submit == "function") {
        var realSubmit = textarea.form.submit;
        function wrappedSubmit() {
          save();
          textarea.form.submit = realSubmit;
          textarea.form.submit();
          textarea.form.submit = wrappedSubmit;
        }
        textarea.form.submit = wrappedSubmit;
      }
    }

    textarea.style.display = "none";
    var instance = CodeMirror(function(node) {
      textarea.parentNode.insertBefore(node, textarea.nextSibling);
    }, options);
    instance.save = save;
    instance.toTextArea = function() {
      save();
      textarea.parentNode.removeChild(instance.getWrapperElement());
      textarea.style.display = "";
      if (textarea.form) {
        rmSubmit();
        if (typeof textarea.form.submit == "function")
          textarea.form.submit = realSubmit;
      }
    };
    return instance;
  };

  // Utility functions for working with state. Exported because modes
  // sometimes need to do this.
  function copyState(mode, state) {
    if (state === true) return state;
    if (mode.copyState) return mode.copyState(state);
    var nstate = {};
    for (var n in state) {
      var val = state[n];
      if (val instanceof Array) val = val.concat([]);
      nstate[n] = val;
    }
    return nstate;
  }
  CodeMirror.startState = startState;
  function startState(mode, a1, a2) {
    return mode.startState ? mode.startState(a1, a2) : true;
  }
  CodeMirror.copyState = copyState;

  // The character stream used by a mode's parser.
  function StringStream(string) {
    this.pos = this.start = 0;
    this.string = string;
  }
  StringStream.prototype = {
    eol: function() {return this.pos >= this.string.length;},
    sol: function() {return this.pos == 0;},
    peek: function() {return this.string.charAt(this.pos);},
    next: function() {
      if (this.pos < this.string.length)
        return this.string.charAt(this.pos++);
    },
    eat: function(match) {
      var ch = this.string.charAt(this.pos);
      if (typeof match == "string") var ok = ch == match;
      else var ok = ch && (match.test ? match.test(ch) : match(ch));
      if (ok) {++this.pos; return ch;}
    },
    eatWhile: function(match) {
      var start = this.pos;
      while (this.eat(match)){}
      return this.pos > start;
    },
    eatSpace: function() {
      var start = this.pos;
      while (/[\s\u00a0]/.test(this.string.charAt(this.pos))) ++this.pos;
      return this.pos > start;
    },
    skipToEnd: function() {this.pos = this.string.length;},
    skipTo: function(ch) {
      var found = this.string.indexOf(ch, this.pos);
      if (found > -1) {this.pos = found; return true;}
    },
    backUp: function(n) {this.pos -= n;},
    column: function() {return countColumn(this.string, this.start);},
    indentation: function() {return countColumn(this.string);},
    match: function(pattern, consume, caseInsensitive) {
      if (typeof pattern == "string") {
        function cased(str) {return caseInsensitive ? str.toLowerCase() : str;}
        if (cased(this.string).indexOf(cased(pattern), this.pos) == this.pos) {
          if (consume !== false) this.pos += pattern.length;
          return true;
        }
      }
      else {
        var match = this.string.slice(this.pos).match(pattern);
        if (match && consume !== false) this.pos += match[0].length;
        return match;
      }
    },
    current: function(){return this.string.slice(this.start, this.pos);}
  };
  CodeMirror.StringStream = StringStream;

  // Line objects. These hold state related to a line, including
  // highlighting info (the styles array).
  function Line(text, styles) {
    this.styles = styles || [text, null];
    this.stateAfter = null;
    this.text = text;
    this.marked = this.gutterMarker = this.className = null;
  }
  Line.prototype = {
    // Replace a piece of a line, keeping the styles around it intact.
    replace: function(from, to, text) {
      var st = [], mk = this.marked;
      copyStyles(0, from, this.styles, st);
      if (text) st.push(text, null);
      copyStyles(to, this.text.length, this.styles, st);
      this.styles = st;
      this.text = this.text.slice(0, from) + text + this.text.slice(to);
      this.stateAfter = null;
      if (mk) {
        var diff = text.length - (to - from), end = this.text.length;
        function fix(n) {return n <= Math.min(to, to + diff) ? n : n + diff;}
        for (var i = 0; i < mk.length; ++i) {
          var mark = mk[i], del = false;
          if (mark.from >= end) del = true;
          else {mark.from = fix(mark.from); if (mark.to != null) mark.to = fix(mark.to);}
          if (del || mark.from >= mark.to) {mk.splice(i, 1); i--;}
        }
      }
    },
    // Split a line in two, again keeping styles intact.
    split: function(pos, textBefore) {
      var st = [textBefore, null];
      copyStyles(pos, this.text.length, this.styles, st);
      return new Line(textBefore + this.text.slice(pos), st);
    },
    addMark: function(from, to, style) {
      var mk = this.marked, mark = {from: from, to: to, style: style};
      if (this.marked == null) this.marked = [];
      this.marked.push(mark);
      this.marked.sort(function(a, b){return a.from - b.from;});
      return mark;
    },
    removeMark: function(mark) {
      var mk = this.marked;
      if (!mk) return;
      for (var i = 0; i < mk.length; ++i)
        if (mk[i] == mark) {mk.splice(i, 1); break;}
    },
    // Run the given mode's parser over a line, update the styles
    // array, which contains alternating fragments of text and CSS
    // classes.
    highlight: function(mode, state) {
      var stream = new StringStream(this.text), st = this.styles, pos = 0;
      var changed = false, curWord = st[0], prevWord;
      if (this.text == "" && mode.blankLine) mode.blankLine(state);
      while (!stream.eol()) {
        var style = mode.token(stream, state);
        var substr = this.text.slice(stream.start, stream.pos);
        stream.start = stream.pos;
        if (pos && st[pos-1] == style)
          st[pos-2] += substr;
        else if (substr) {
          if (!changed && (st[pos+1] != style || (pos && st[pos-2] != prevWord))) changed = true;
          st[pos++] = substr; st[pos++] = style;
          prevWord = curWord; curWord = st[pos];
        }
        // Give up when line is ridiculously long
        if (stream.pos > 5000) {
          st[pos++] = this.text.slice(stream.pos); st[pos++] = null;
          break;
        }
      }
      if (st.length != pos) {st.length = pos; changed = true;}
      if (pos && st[pos-2] != prevWord) changed = true;
      // Short lines with simple highlights return null, and are
      // counted as changed by the driver because they are likely to
      // highlight the same way in various contexts.
      return changed || (st.length < 5 && this.text.length < 10 ? null : false);
    },
    // Fetch the parser token for a given character. Useful for hacks
    // that want to inspect the mode state (say, for completion).
    getTokenAt: function(mode, state, ch) {
      var txt = this.text, stream = new StringStream(txt);
      while (stream.pos < ch && !stream.eol()) {
        stream.start = stream.pos;
        var style = mode.token(stream, state);
      }
      return {start: stream.start,
              end: stream.pos,
              string: stream.current(),
              className: style || null,
              state: state};
    },
    indentation: function() {return countColumn(this.text);},
    // Produces an HTML fragment for the line, taking selection,
    // marking, and highlighting into account.
    getHTML: function(sfrom, sto, includePre, endAt) {
      var html = [];
      if (includePre)
        html.push(this.className ? '<pre class="' + this.className + '">': "<pre>");
      function span(text, style) {
        if (!text) return;
        if (style) html.push('<span class="', style, '">', htmlEscape(text), "</span>");
        else html.push(htmlEscape(text));
      }
      var st = this.styles, allText = this.text, marked = this.marked;
      if (sfrom == sto) sfrom = null;
      var len = allText.length;
      if (endAt != null) len = Math.min(endAt, len);

      if (!allText && endAt == null)
        span(" ", sfrom != null && sto == null ? "CodeMirror-selected" : null);
      else if (!marked && sfrom == null)
        for (var i = 0, ch = 0; ch < len; i+=2) {
          var str = st[i], l = str.length;
          if (ch + l > len) str = str.slice(0, len - ch);
          ch += l;
          span(str, "cm-" + st[i+1]);
        }
      else {
        var pos = 0, i = 0, text = "", style, sg = 0;
        var markpos = -1, mark = null;
        function nextMark() {
          if (marked) {
            markpos += 1;
            mark = (markpos < marked.length) ? marked[markpos] : null;
          }
        }
        nextMark();
        while (pos < len) {
          var upto = len;
          var extraStyle = "";
          if (sfrom != null) {
            if (sfrom > pos) upto = sfrom;
            else if (sto == null || sto > pos) {
              extraStyle = " CodeMirror-selected";
              if (sto != null) upto = Math.min(upto, sto);
            }
          }
          while (mark && mark.to != null && mark.to <= pos) nextMark();
          if (mark) {
            if (mark.from > pos) upto = Math.min(upto, mark.from);
            else {
              extraStyle += " " + mark.style;
              if (mark.to != null) upto = Math.min(upto, mark.to);
            }
          }
          for (;;) {
            var end = pos + text.length;
            var appliedStyle = style;
            if (extraStyle) appliedStyle = style ? style + extraStyle : extraStyle;
            span(end > upto ? text.slice(0, upto - pos) : text, appliedStyle);
            if (end >= upto) {text = text.slice(upto - pos); pos = upto; break;}
            pos = end;
            text = st[i++]; style = "cm-" + st[i++];
          }
        }
        if (sfrom != null && sto == null) span(" ", "CodeMirror-selected");
      }
      if (includePre) html.push("</pre>");
      return html.join("");
    }
  };
  // Utility used by replace and split above
  function copyStyles(from, to, source, dest) {
    for (var i = 0, pos = 0, state = 0; pos < to; i+=2) {
      var part = source[i], end = pos + part.length;
      if (state == 0) {
        if (end > from) dest.push(part.slice(from - pos, Math.min(part.length, to - pos)), source[i+1]);
        if (end >= from) state = 1;
      }
      else if (state == 1) {
        if (end > to) dest.push(part.slice(0, to - pos), source[i+1]);
        else dest.push(part, source[i+1]);
      }
      pos = end;
    }
  }

  // The history object 'chunks' changes that are made close together
  // and at almost the same time into bigger undoable units.
  function History() {
    this.time = 0;
    this.done = []; this.undone = [];
  }
  History.prototype = {
    addChange: function(start, added, old) {
      this.undone.length = 0;
      var time = +new Date, last = this.done[this.done.length - 1];
      if (time - this.time > 400 || !last ||
          last.start > start + added || last.start + last.added < start - last.added + last.old.length)
        this.done.push({start: start, added: added, old: old});
      else {
        var oldoff = 0;
        if (start < last.start) {
          for (var i = last.start - start - 1; i >= 0; --i)
            last.old.unshift(old[i]);
          last.added += last.start - start;
          last.start = start;
        }
        else if (last.start < start) {
          oldoff = start - last.start;
          added += oldoff;
        }
        for (var i = last.added - oldoff, e = old.length; i < e; ++i)
          last.old.push(old[i]);
        if (last.added < added) last.added = added;
      }
      this.time = time;
    }
  };

  function stopMethod() {e_stop(this);}
  // Ensure an event has a stop method.
  function addStop(event) {
    if (!event.stop) event.stop = stopMethod;
    return event;
  }

  function e_preventDefault(e) {
    if (e.preventDefault) e.preventDefault();
    else e.returnValue = false;
  }
  function e_stopPropagation(e) {
    if (e.stopPropagation) e.stopPropagation();
    else e.cancelBubble = true;
  }
  function e_stop(e) {e_preventDefault(e); e_stopPropagation(e);}
  function e_target(e) {return e.target || e.srcElement;}
  function e_button(e) {
    if (e.which) return e.which;
    else if (e.button & 1) return 1;
    else if (e.button & 2) return 3;
    else if (e.button & 4) return 2;
  }

  // Event handler registration. If disconnect is true, it'll return a
  // function that unregisters the handler.
  function connect(node, type, handler, disconnect) {
    function wrapHandler(event) {handler(event || window.event);}
    if (typeof node.addEventListener == "function") {
      node.addEventListener(type, wrapHandler, false);
      if (disconnect) return function() {node.removeEventListener(type, wrapHandler, false);};
    }
    else {
      node.attachEvent("on" + type, wrapHandler);
      if (disconnect) return function() {node.detachEvent("on" + type, wrapHandler);};
    }
  }

  function Delayed() {this.id = null;}
  Delayed.prototype = {set: function(ms, f) {clearTimeout(this.id); this.id = setTimeout(f, ms);}};

  // Some IE versions don't preserve whitespace when setting the
  // innerHTML of a PRE tag.
  var badInnerHTML = (function() {
    var pre = document.createElement("pre");
    pre.innerHTML = " "; return !pre.innerHTML;
  })();

  var gecko = /gecko\/\d{7}/i.test(navigator.userAgent);
  var ie = /MSIE \d/.test(navigator.userAgent);
  var safari = /Apple Computer/.test(navigator.vendor);

  var lineSep = "\n";
  // Feature-detect whether newlines in textareas are converted to \r\n
  (function () {
    var te = document.createElement("textarea");
    te.value = "foo\nbar";
    if (te.value.indexOf("\r") > -1) lineSep = "\r\n";
  }());

  var tabSize = 8;
  var mac = /Mac/.test(navigator.platform);
  var movementKeys = {};
  for (var i = 35; i <= 40; ++i)
    movementKeys[i] = movementKeys["c" + i] = true;

  // Counts the column offset in a string, taking tabs into account.
  // Used mostly to find indentation.
  function countColumn(string, end) {
    if (end == null) {
      end = string.search(/[^\s\u00a0]/);
      if (end == -1) end = string.length;
    }
    for (var i = 0, n = 0; i < end; ++i) {
      if (string.charAt(i) == "\t") n += tabSize - (n % tabSize);
      else ++n;
    }
    return n;
  }

  function computedStyle(elt) {
    if (elt.currentStyle) return elt.currentStyle;
    return window.getComputedStyle(elt, null);
  }
  // Find the position of an element by following the offsetParent chain.
  // If screen==true, it returns screen (rather than page) coordinates.
  function eltOffset(node, screen) {
    var doc = node.ownerDocument.body;
    var x = 0, y = 0, skipDoc = false;
    for (var n = node; n; n = n.offsetParent) {
      x += n.offsetLeft; y += n.offsetTop;
      if (screen && computedStyle(n).position == "fixed")
        skipDoc = true;
    }
    var e = screen && !skipDoc ? null : doc;
    for (var n = node.parentNode; n != e; n = n.parentNode)
      if (n.scrollLeft != null) { x -= n.scrollLeft; y -= n.scrollTop;}
    return {left: x, top: y};
  }
  // Get a node's text content.
  function eltText(node) {
    return node.textContent || node.innerText || node.nodeValue || "";
  }

  // Operations on {line, ch} objects.
  function posEq(a, b) {return a.line == b.line && a.ch == b.ch;}
  function posLess(a, b) {return a.line < b.line || (a.line == b.line && a.ch < b.ch);}
  function copyPos(x) {return {line: x.line, ch: x.ch};}

  var escapeElement = document.createElement("div");
  function htmlEscape(str) {
    escapeElement.innerText = escapeElement.textContent = str;
    return escapeElement.innerHTML;
  }
  CodeMirror.htmlEscape = htmlEscape;

  // Used to position the cursor after an undo/redo by finding the
  // last edited character.
  function editEnd(from, to) {
    if (!to) return from ? from.length : 0;
    if (!from) return to.length;
    for (var i = from.length, j = to.length; i >= 0 && j >= 0; --i, --j)
      if (from.charAt(i) != to.charAt(j)) break;
    return j + 1;
  }

  function indexOf(collection, elt) {
    if (collection.indexOf) return collection.indexOf(elt);
    for (var i = 0, e = collection.length; i < e; ++i)
      if (collection[i] == elt) return i;
    return -1;
  }

  // See if "".split is the broken IE version, if so, provide an
  // alternative way to split lines.
  var splitLines, selRange, setSelRange;
  if ("\n\nb".split(/\n/).length != 3)
    splitLines = function(string) {
      var pos = 0, nl, result = [];
      while ((nl = string.indexOf("\n", pos)) > -1) {
        result.push(string.slice(pos, string.charAt(nl-1) == "\r" ? nl - 1 : nl));
        pos = nl + 1;
      }
      result.push(string.slice(pos));
      return result;
    };
  else
    splitLines = function(string){return string.split(/\r?\n/);};
  CodeMirror.splitLines = splitLines;

  // Sane model of finding and setting the selection in a textarea
  if (window.getSelection) {
    selRange = function(te) {
      try {return {start: te.selectionStart, end: te.selectionEnd};}
      catch(e) {return null;}
    };
    if (safari)
      // On Safari, selection set with setSelectionRange are in a sort
      // of limbo wrt their anchor. If you press shift-left in them,
      // the anchor is put at the end, and the selection expanded to
      // the left. If you press shift-right, the anchor ends up at the
      // front. This is not what CodeMirror wants, so it does a
      // spurious modify() call to get out of limbo.
      setSelRange = function(te, start, end) {
        if (start == end)
          te.setSelectionRange(start, end);
        else {
          te.setSelectionRange(start, end - 1);
          window.getSelection().modify("extend", "forward", "character");
        }
      };
    else
      setSelRange = function(te, start, end) {
        try {te.setSelectionRange(start, end);}
        catch(e) {} // Fails on Firefox when textarea isn't part of the document
      };
  }
  // IE model. Don't ask.
  else {
    selRange = function(te) {
      try {var range = te.ownerDocument.selection.createRange();}
      catch(e) {return null;}
      if (!range || range.parentElement() != te) return null;
      var val = te.value, len = val.length, localRange = te.createTextRange();
      localRange.moveToBookmark(range.getBookmark());
      var endRange = te.createTextRange();
      endRange.collapse(false);

      if (localRange.compareEndPoints("StartToEnd", endRange) > -1)
        return {start: len, end: len};

      var start = -localRange.moveStart("character", -len);
      for (var i = val.indexOf("\r"); i > -1 && i < start; i = val.indexOf("\r", i+1), start++) {}

      if (localRange.compareEndPoints("EndToEnd", endRange) > -1)
        return {start: start, end: len};

      var end = -localRange.moveEnd("character", -len);
      for (var i = val.indexOf("\r"); i > -1 && i < end; i = val.indexOf("\r", i+1), end++) {}
      return {start: start, end: end};
    };
    setSelRange = function(te, start, end) {
      var range = te.createTextRange();
      range.collapse(true);
      var endrange = range.duplicate();
      var newlines = 0, txt = te.value;
      for (var pos = txt.indexOf("\n"); pos > -1 && pos < start; pos = txt.indexOf("\n", pos + 1))
        ++newlines;
      range.move("character", start - newlines);
      for (; pos > -1 && pos < end; pos = txt.indexOf("\n", pos + 1))
        ++newlines;
      endrange.move("character", end - newlines);
      range.setEndPoint("EndToEnd", endrange);
      range.select();
    };
  }

  CodeMirror.defineMode("null", function() {
    return {token: function(stream) {stream.skipToEnd();}};
  });
  CodeMirror.defineMIME("text/plain", "null");

  return CodeMirror;
})()
;

// mode/javascript/javascript.js

CodeMirror.defineMode("javascript", function(config, parserConfig) {
  var indentUnit = config.indentUnit;
  var jsonMode = parserConfig.json;

  // Tokenizer

  var keywords = function(){
    function kw(type) {return {type: type, style: "keyword"};}
    var A = kw("keyword a"), B = kw("keyword b"), C = kw("keyword c");
    var operator = kw("operator"), atom = {type: "atom", style: "atom"};
    return {
      "if": A, "while": A, "with": A, "else": B, "do": B, "try": B, "finally": B,
      "return": C, "break": C, "continue": C, "new": C, "delete": C, "throw": C,
      "var": kw("var"), "function": kw("function"), "catch": kw("catch"),
      "for": kw("for"), "switch": kw("switch"), "case": kw("case"), "default": kw("default"),
      "in": operator, "typeof": operator, "instanceof": operator,
      "true": atom, "false": atom, "null": atom, "undefined": atom, "NaN": atom, "Infinity": atom
    };
  }();

  var isOperatorChar = /[+\-*&%=<>!?|]/;

  function chain(stream, state, f) {
    state.tokenize = f;
    return f(stream, state);
  }

  function nextUntilUnescaped(stream, end) {
    var escaped = false, next;
    while ((next = stream.next()) != null) {
      if (next == end && !escaped)
        return false;
      escaped = !escaped && next == "\\";
    }
    return escaped;
  }

  // Used as scratch variables to communicate multiple values without
  // consing up tons of objects.
  var type, content;
  function ret(tp, style, cont) {
    type = tp; content = cont;
    return style;
  }

  function jsTokenBase(stream, state) {
    var ch = stream.next();
    if (ch == '"' || ch == "'")
      return chain(stream, state, jsTokenString(ch));
    else if (/[\[\]{}\(\),;\:\.]/.test(ch))
      return ret(ch);
    else if (ch == "0" && stream.eat(/x/i)) {
      stream.eatWhile(/[\da-f]/i);
      return ret("number", "number");
    }      
    else if (/\d/.test(ch)) {
      stream.match(/^\d*(?:\.\d*)?(?:e[+\-]?\d+)?/);
      return ret("number", "number");
    }
    else if (ch == "/") {
      if (stream.eat("*")) {
        return chain(stream, state, jsTokenComment);
      }
      else if (stream.eat("/")) {
        stream.skipToEnd();
        return ret("comment", "comment");
      }
      else if (state.reAllowed) {
        nextUntilUnescaped(stream, "/");
        stream.eatWhile(/[gimy]/); // 'y' is "sticky" option in Mozilla
        return ret("regexp", "string");
      }
      else {
        stream.eatWhile(isOperatorChar);
        return ret("operator", null, stream.current());
      }
    }
    else if (isOperatorChar.test(ch)) {
      stream.eatWhile(isOperatorChar);
      return ret("operator", null, stream.current());
    }
    else {
      stream.eatWhile(/[\w\$_]/);
      var word = stream.current(), known = keywords.propertyIsEnumerable(word) && keywords[word];
      return known ? ret(known.type, known.style, word) :
                     ret("variable", "variable", word);
    }
  }

  function jsTokenString(quote) {
    return function(stream, state) {
      if (!nextUntilUnescaped(stream, quote))
        state.tokenize = jsTokenBase;
      return ret("string", "string");
    };
  }

  function jsTokenComment(stream, state) {
    var maybeEnd = false, ch;
    while (ch = stream.next()) {
      if (ch == "/" && maybeEnd) {
        state.tokenize = jsTokenBase;
        break;
      }
      maybeEnd = (ch == "*");
    }
    return ret("comment", "comment");
  }

  // Parser

  var atomicTypes = {"atom": true, "number": true, "variable": true, "string": true, "regexp": true};

  function JSLexical(indented, column, type, align, prev, info) {
    this.indented = indented;
    this.column = column;
    this.type = type;
    this.prev = prev;
    this.info = info;
    if (align != null) this.align = align;
  }

  function inScope(state, varname) {
    for (var v = state.localVars; v; v = v.next)
      if (v.name == varname) return true;
  }

  function parseJS(state, style, type, content, stream) {
    var cc = state.cc;
    // Communicate our context to the combinators.
    // (Less wasteful than consing up a hundred closures on every call.)
    cx.state = state; cx.stream = stream; cx.marked = null, cx.cc = cc;
  
    if (!state.lexical.hasOwnProperty("align"))
      state.lexical.align = true;

    while(true) {
      var combinator = cc.length ? cc.pop() : jsonMode ? expression : statement;
      if (combinator(type, content)) {
        while(cc.length && cc[cc.length - 1].lex)
          cc.pop()();
        if (cx.marked) return cx.marked;
        if (type == "variable" && inScope(state, content)) return "variable-2";
        return style;
      }
    }
  }

  // Combinator utils

  var cx = {state: null, column: null, marked: null, cc: null};
  function pass() {
    for (var i = arguments.length - 1; i >= 0; i--) cx.cc.push(arguments[i]);
  }
  function cont() {
    pass.apply(null, arguments);
    return true;
  }
  function register(varname) {
    var state = cx.state;
    if (state.context) {
      cx.marked = "def";
      for (var v = state.localVars; v; v = v.next)
        if (v.name == varname) return;
      state.localVars = {name: varname, next: state.localVars};
    }
  }

  // Combinators

  var defaultVars = {name: "this", next: {name: "arguments"}};
  function pushcontext() {
    if (!cx.state.context) cx.state.localVars = defaultVars;
    cx.state.context = {prev: cx.state.context, vars: cx.state.localVars};
  }
  function popcontext() {
    cx.state.localVars = cx.state.context.vars;
    cx.state.context = cx.state.context.prev;
  }
  function pushlex(type, info) {
    var result = function() {
      var state = cx.state;
      state.lexical = new JSLexical(state.indented, cx.stream.column(), type, null, state.lexical, info)
    };
    result.lex = true;
    return result;
  }
  function poplex() {
    var state = cx.state;
    if (state.lexical.prev) {
      if (state.lexical.type == ")")
        state.indented = state.lexical.indented;
      state.lexical = state.lexical.prev;
    }
  }
  poplex.lex = true;

  function expect(wanted) {
    return function expecting(type) {
      if (type == wanted) return cont();
      else if (wanted == ";") return pass();
      else return cont(arguments.callee);
    };
  }

  function statement(type) {
    if (type == "var") return cont(pushlex("vardef"), vardef1, expect(";"), poplex);
    if (type == "keyword a") return cont(pushlex("form"), expression, statement, poplex);
    if (type == "keyword b") return cont(pushlex("form"), statement, poplex);
    if (type == "{") return cont(pushlex("}"), block, poplex);
    if (type == ";") return cont();
    if (type == "function") return cont(functiondef);
    if (type == "for") return cont(pushlex("form"), expect("("), pushlex(")"), forspec1, expect(")"),
                                      poplex, statement, poplex);
    if (type == "variable") return cont(pushlex("stat"), maybelabel);
    if (type == "switch") return cont(pushlex("form"), expression, pushlex("}", "switch"), expect("{"),
                                         block, poplex, poplex);
    if (type == "case") return cont(expression, expect(":"));
    if (type == "default") return cont(expect(":"));
    if (type == "catch") return cont(pushlex("form"), pushcontext, expect("("), funarg, expect(")"),
                                        statement, poplex, popcontext);
    return pass(pushlex("stat"), expression, expect(";"), poplex);
  }
  function expression(type) {
    if (atomicTypes.hasOwnProperty(type)) return cont(maybeoperator);
    if (type == "function") return cont(functiondef);
    if (type == "keyword c") return cont(expression);
    if (type == "(") return cont(pushlex(")"), expression, expect(")"), poplex, maybeoperator);
    if (type == "operator") return cont(expression);
    if (type == "[") return cont(pushlex("]"), commasep(expression, "]"), poplex, maybeoperator);
    if (type == "{") return cont(pushlex("}"), commasep(objprop, "}"), poplex, maybeoperator);
    return cont();
  }
  function maybeoperator(type, value) {
    if (type == "operator" && /\+\+|--/.test(value)) return cont(maybeoperator);
    if (type == "operator") return cont(expression);
    if (type == ";") return;
    if (type == "(") return cont(pushlex(")"), commasep(expression, ")"), poplex, maybeoperator);
    if (type == ".") return cont(property, maybeoperator);
    if (type == "[") return cont(pushlex("]"), expression, expect("]"), poplex, maybeoperator);
  }
  function maybelabel(type) {
    if (type == ":") return cont(poplex, statement);
    return pass(maybeoperator, expect(";"), poplex);
  }
  function property(type) {
    if (type == "variable") {cx.marked = "property"; return cont();}
  }
  function objprop(type) {
    if (type == "variable") cx.marked = "property";
    if (atomicTypes.hasOwnProperty(type)) return cont(expect(":"), expression);
  }
  function commasep(what, end) {
    function proceed(type) {
      if (type == ",") return cont(what, proceed);
      if (type == end) return cont();
      return cont(expect(end));
    }
    return function commaSeparated(type) {
      if (type == end) return cont();
      else return pass(what, proceed);
    };
  }
  function block(type) {
    if (type == "}") return cont();
    return pass(statement, block);
  }
  function vardef1(type, value) {
    if (type == "variable"){register(value); return cont(vardef2);}
    return cont();
  }
  function vardef2(type, value) {
    if (value == "=") return cont(expression, vardef2);
    if (type == ",") return cont(vardef1);
  }
  function forspec1(type) {
    if (type == "var") return cont(vardef1, forspec2);
    if (type == ";") return pass(forspec2);
    if (type == "variable") return cont(formaybein);
    return pass(forspec2);
  }
  function formaybein(type, value) {
    if (value == "in") return cont(expression);
    return cont(maybeoperator, forspec2);
  }
  function forspec2(type, value) {
    if (type == ";") return cont(forspec3);
    if (value == "in") return cont(expression);
    return cont(expression, expect(";"), forspec3);
  }
  function forspec3(type) {
    if (type != ")") cont(expression);
  }
  function functiondef(type, value) {
    if (type == "variable") {register(value); return cont(functiondef);}
    if (type == "(") return cont(pushlex(")"), pushcontext, commasep(funarg, ")"), poplex, statement, popcontext);
  }
  function funarg(type, value) {
    if (type == "variable") {register(value); return cont();}
  }

  // Interface

  return {
    startState: function(basecolumn) {
      return {
        tokenize: jsTokenBase,
        reAllowed: true,
        cc: [],
        lexical: new JSLexical((basecolumn || 0) - indentUnit, 0, "block", false),
        localVars: null,
        context: null,
        indented: 0
      };
    },

    token: function(stream, state) {
      if (stream.sol()) {
        if (!state.lexical.hasOwnProperty("align"))
          state.lexical.align = false;
        state.indented = stream.indentation();
      }
      if (stream.eatSpace()) return null;
      var style = state.tokenize(stream, state);
      if (type == "comment") return style;
      state.reAllowed = type == "operator" || type == "keyword c" || type.match(/^[\[{}\(,;:]$/);
      return parseJS(state, style, type, content, stream);
    },

    indent: function(state, textAfter) {
      if (state.tokenize != jsTokenBase) return 0;
      var firstChar = textAfter && textAfter.charAt(0), lexical = state.lexical,
          type = lexical.type, closing = firstChar == type;
      if (type == "vardef") return lexical.indented + 4;
      else if (type == "form" && firstChar == "{") return lexical.indented;
      else if (type == "stat" || type == "form") return lexical.indented + indentUnit;
      else if (lexical.info == "switch" && !closing)
        return lexical.indented + (/^(?:case|default)\b/.test(textAfter) ? indentUnit : 2 * indentUnit);
      else if (lexical.align) return lexical.column + (closing ? 0 : 1);
      else return lexical.indented + (closing ? 0 : indentUnit);
    },

    electricChars: ":{}"
  };
});

CodeMirror.defineMIME("text/javascript", "javascript");
CodeMirror.defineMIME("application/json", {name: "javascript", json: true});

// mode/css/css.js

CodeMirror.defineMode("css", function(config) {
  var indentUnit = config.indentUnit, type;
  function ret(style, tp) {type = tp; return style;}

  function tokenBase(stream, state) {
    var ch = stream.next();
    if (ch == "@") {stream.eatWhile(/\w/); return ret("meta", stream.current());}
    else if (ch == "/" && stream.eat("*")) {
      state.tokenize = tokenCComment;
      return tokenCComment(stream, state);
    }
    else if (ch == "<" && stream.eat("!")) {
      state.tokenize = tokenSGMLComment;
      return tokenSGMLComment(stream, state);
    }
    else if (ch == "=") ret(null, "compare");
    else if ((ch == "~" || ch == "|") && stream.eat("=")) return ret(null, "compare");
    else if (ch == "\"" || ch == "'") {
      state.tokenize = tokenString(ch);
      return state.tokenize(stream, state);
    }
    else if (ch == "#") {
      stream.eatWhile(/\w/);
      return ret("atom", "hash");
    }
    else if (ch == "!") {
      stream.match(/^\s*\w*/);
      return ret("keyword", "important");
    }
    else if (/\d/.test(ch)) {
      stream.eatWhile(/[\w.%]/);
      return ret("number", "unit");
    }
    else if (/[,.+>*\/]/.test(ch)) {
      return ret(null, "select-op");
    }
    else if (/[;{}:\[\]]/.test(ch)) {
      return ret(null, ch);
    }
    else {
      stream.eatWhile(/[\w\\\-_]/);
      return ret("variable", "variable");
    }
  }

  function tokenCComment(stream, state) {
    var maybeEnd = false, ch;
    while ((ch = stream.next()) != null) {
      if (maybeEnd && ch == "/") {
        state.tokenize = tokenBase;
        break;
      }
      maybeEnd = (ch == "*");
    }
    return ret("comment", "comment");
  }

  function tokenSGMLComment(stream, state) {
    var dashes = 0, ch;
    while ((ch = stream.next()) != null) {
      if (dashes >= 2 && ch == ">") {
        state.tokenize = tokenBase;
        break;
      }
      dashes = (ch == "-") ? dashes + 1 : 0;
    }
    return ret("comment", "comment");
  }

  function tokenString(quote) {
    return function(stream, state) {
      var escaped = false, ch;
      while ((ch = stream.next()) != null) {
        if (ch == quote && !escaped)
          break;
        escaped = !escaped && ch == "\\";
      }
      if (!escaped) state.tokenize = tokenBase;
      return ret("string", "string");
    };
  }

  return {
    startState: function(base) {
      return {tokenize: tokenBase,
              baseIndent: base || 0,
              stack: []};
    },

    token: function(stream, state) {
      if (stream.eatSpace()) return null;
      var style = state.tokenize(stream, state);

      var context = state.stack[state.stack.length-1];
      if (type == "hash" && context == "rule") style = "atom";
      else if (style == "variable") {
        if (context == "rule") style = "number";
        else if (!context || context == "@media{") style = "tag";
      }

      if (context == "rule" && /^[\{\};]$/.test(type))
        state.stack.pop();
      if (type == "{") {
        if (context == "@media") state.stack[state.stack.length-1] = "@media{";
        else state.stack.push("{");
      }
      else if (type == "}") state.stack.pop();
      else if (type == "@media") state.stack.push("@media");
      else if (context == "{" && type != "comment") state.stack.push("rule");
      return style;
    },

    indent: function(state, textAfter) {
      var n = state.stack.length;
      if (/^\}/.test(textAfter))
        n -= state.stack[state.stack.length-1] == "rule" ? 2 : 1;
      return state.baseIndent + n * indentUnit;
    },

    electricChars: "}"
  };
});

CodeMirror.defineMIME("text/css", "css");

// mode/clike/clike.js

CodeMirror.defineMode("clike", function(config, parserConfig) {
  var indentUnit = config.indentUnit,
      keywords = parserConfig.keywords || {},
      blockKeywords = parserConfig.blockKeywords || {},
      atoms = parserConfig.atoms || {},
      hooks = parserConfig.hooks || {},
      multiLineStrings = parserConfig.multiLineStrings;
  var isOperatorChar = /[+\-*&%=<>!?|\/]/;

  var curPunc;

  function tokenBase(stream, state) {
    var ch = stream.next();
    if (hooks[ch]) {
      var result = hooks[ch](stream, state);
      if (result !== false) return result;
    }
    if (ch == '"' || ch == "'") {
      state.tokenize = tokenString(ch);
      return state.tokenize(stream, state);
    }
    if (/[\[\]{}\(\),;\:\.]/.test(ch)) {
      curPunc = ch;
      return null
    }
    if (/\d/.test(ch)) {
      stream.eatWhile(/[\w\.]/);
      return "number";
    }
    if (ch == "/") {
      if (stream.eat("*")) {
        state.tokenize = tokenComment;
        return tokenComment(stream, state);
      }
      if (stream.eat("/")) {
        stream.skipToEnd();
        return "comment";
      }
    }
    if (isOperatorChar.test(ch)) {
      stream.eatWhile(isOperatorChar);
      return "operator";
    }
    stream.eatWhile(/[\w\$_]/);
    var cur = stream.current();
    if (keywords.propertyIsEnumerable(cur)) {
      if (blockKeywords.propertyIsEnumerable(cur)) curPunc = "newstatement";
      return "keyword";
    }
    if (atoms.propertyIsEnumerable(cur)) return "atom";
    return "word";
  }

  function tokenString(quote) {
    return function(stream, state) {
      var escaped = false, next, end = false;
      while ((next = stream.next()) != null) {
        if (next == quote && !escaped) {end = true; break;}
        escaped = !escaped && next == "\\";
      }
      if (end || !(escaped || multiLineStrings))
        state.tokenize = tokenBase;
      return "string";
    };
  }

  function tokenComment(stream, state) {
    var maybeEnd = false, ch;
    while (ch = stream.next()) {
      if (ch == "/" && maybeEnd) {
        state.tokenize = tokenBase;
        break;
      }
      maybeEnd = (ch == "*");
    }
    return "comment";
  }

  function Context(indented, column, type, align, prev) {
    this.indented = indented;
    this.column = column;
    this.type = type;
    this.align = align;
    this.prev = prev;
  }
  function pushContext(state, col, type) {
    return state.context = new Context(state.indented, col, type, null, state.context);
  }
  function popContext(state) {
    var t = state.context.type;
    if (t == ")" || t == "]" || t == "}")
      state.indented = state.context.indented;
    return state.context = state.context.prev;
  }

  // Interface

  return {
    startState: function(basecolumn) {
      return {
        tokenize: null,
        context: new Context((basecolumn || 0) - indentUnit, 0, "top", false),
        indented: 0,
        startOfLine: true
      };
    },

    token: function(stream, state) {
      var ctx = state.context;
      if (stream.sol()) {
        if (ctx.align == null) ctx.align = false;
        state.indented = stream.indentation();
        state.startOfLine = true;
      }
      if (stream.eatSpace()) return null;
      curPunc = null;
      var style = (state.tokenize || tokenBase)(stream, state);
      if (style == "comment" || style == "meta") return style;
      if (ctx.align == null) ctx.align = true;

      if ((curPunc == ";" || curPunc == ":") && ctx.type == "statement") popContext(state);
      else if (curPunc == "{") pushContext(state, stream.column(), "}");
      else if (curPunc == "[") pushContext(state, stream.column(), "]");
      else if (curPunc == "(") pushContext(state, stream.column(), ")");
      else if (curPunc == "}") {
        while (ctx.type == "statement") ctx = popContext(state);
        if (ctx.type == "}") ctx = popContext(state);
        while (ctx.type == "statement") ctx = popContext(state);
      }
      else if (curPunc == ctx.type) popContext(state);
      else if (ctx.type == "}" || ctx.type == "top" || (ctx.type == "statement" && curPunc == "newstatement"))
        pushContext(state, stream.column(), "statement");
      state.startOfLine = false;
      return style;
    },

    indent: function(state, textAfter) {
      if (state.tokenize != tokenBase && state.tokenize != null) return 0;
      var firstChar = textAfter && textAfter.charAt(0), ctx = state.context, closing = firstChar == ctx.type;
      if (ctx.type == "statement") return ctx.indented + (firstChar == "{" ? 0 : indentUnit);
      else if (ctx.align) return ctx.column + (closing ? 0 : 1);
      else return ctx.indented + (closing ? 0 : indentUnit);
    },

    electricChars: "{}"
  };
});

(function() {
  function words(str) {
    var obj = {}, words = str.split(" ");
    for (var i = 0; i < words.length; ++i) obj[words[i]] = true;
    return obj;
  }
  var cKeywords = "auto if break int case long char register continue return default short do sizeof " +
    "double static else struct entry switch extern typedef float union for unsigned " +
    "goto while enum void const signed volatile";

  function cppHook(stream, state) {
    if (!state.startOfLine) return false;
    stream.skipToEnd();
    return "meta";
  }

  // C#-style strings where "" escapes a quote.
  function tokenAtString(stream, state) {
    var next;
    while ((next = stream.next()) != null) {
      if (next == '"' && !stream.eat('"')) {
        state.tokenize = null;
        break;
      }
    }
    return "string";
  }

  CodeMirror.defineMIME("text/x-csrc", {
    name: "clike",
    keywords: words(cKeywords),
    blockKeywords: words("case do else for if switch while struct"),
    atoms: words("null"),
    hooks: {"#": cppHook}
  });
  CodeMirror.defineMIME("text/x-c++src", {
    name: "clike",
    keywords: words(cKeywords + " asm dynamic_cast namespace reinterpret_cast try bool explicit new " +
                    "static_cast typeid catch operator template typename class friend private " +
                    "this using const_cast inline public throw virtual delete mutable protected " +
                    "wchar_t"),
    blockKeywords: words("catch class do else finally for if struct switch try while"),
    atoms: words("true false null"),
    hooks: {"#": cppHook}
  });
  CodeMirror.defineMIME("text/x-java", {
    name: "clike",
    keywords: words("abstract assert boolean break byte case catch char class const continue default " + 
                    "do double else enum extends final finally float for goto if implements import " +
                    "instanceof int interface long native new package private protected public " +
                    "return short static strictfp super switch synchronized this throw throws transient " +
                    "try void volatile while"),
    blockKeywords: words("catch class do else finally for if switch try while"),
    atoms: words("true false null"),
    hooks: {
      "@": function(stream, state) {
        stream.eatWhile(/[\w\$_]/);
        return "meta";
      }
    }
  });
  CodeMirror.defineMIME("text/x-csharp", {
    name: "clike",
    keywords: words("abstract as base bool break byte case catch char checked class const continue decimal" + 
                    " default delegate do double else enum event explicit extern finally fixed float for" + 
                    " foreach goto if implicit in int interface internal is lock long namespace new object" + 
                    " operator out override params private protected public readonly ref return sbyte sealed short" + 
                    " sizeof stackalloc static string struct switch this throw try typeof uint ulong unchecked" + 
                    " unsafe ushort using virtual void volatile while add alias ascending descending dynamic from get" + 
                    " global group into join let orderby partial remove select set value var yield"),
    blockKeywords: words("catch class do else finally for foreach if struct switch try while"),
    atoms: words("true false null"),
    hooks: {
      "@": function(stream, state) {
        if (stream.eat('"')) {
          state.tokenize = tokenAtString;
          return tokenAtString(stream, state);
        }
        stream.eatWhile(/[\w\$_]/);
        return "meta";
      }
    }
  });
  CodeMirror.defineMIME("text/x-groovy", {
    name: "clike",
    keywords: words("abstract as assert boolean break byte case catch char class const continue def default " +
                    "do double else enum extends final finally float for goto if implements import " +
                    "in instanceof int interface long native new package property private protected public " +
                    "return short static strictfp super switch synchronized this throw throws transient " +
                    "try void volatile while"),
    atoms: words("true false null"),
    hooks: {
      "@": function(stream, state) {
        stream.eatWhile(/[\w\$_]/);
        return "meta";
      }
    }
  });
}());

// mode/xml/xml.js

CodeMirror.defineMode("xml", function(config, parserConfig) {
  var indentUnit = config.indentUnit;
  var Kludges = parserConfig.htmlMode ? {
    autoSelfClosers: {"br": true, "img": true, "hr": true, "link": true, "input": true,
                      "meta": true, "col": true, "frame": true, "base": true, "area": true},
    doNotIndent: {"pre": true, "!cdata": true},
    allowUnquoted: true
  } : {autoSelfClosers: {}, doNotIndent: {"!cdata": true}, allowUnquoted: false};
  var alignCDATA = parserConfig.alignCDATA;

  // Return variables for tokenizers
  var tagName, type;

  function inText(stream, state) {
    function chain(parser) {
      state.tokenize = parser;
      return parser(stream, state);
    }

    var ch = stream.next();
    if (ch == "<") {
      if (stream.eat("!")) {
        if (stream.eat("[")) {
          if (stream.match("CDATA[")) return chain(inBlock("atom", "]]>"));
          else return null;
        }
        else if (stream.match("--")) return chain(inBlock("comment", "-->"));
        else if (stream.match("DOCTYPE", true, true)) {
          stream.eatWhile(/[\w\._\-]/);
          return chain(inBlock("meta", ">"));
        }
        else return null;
      }
      else if (stream.eat("?")) {
        stream.eatWhile(/[\w\._\-]/);
        state.tokenize = inBlock("meta", "?>");
        return "meta";
      }
      else {
        type = stream.eat("/") ? "closeTag" : "openTag";
        stream.eatSpace();
        tagName = "";
        var c;
        while ((c = stream.eat(/[^\s\u00a0=<>\"\'\/?]/))) tagName += c;
        state.tokenize = inTag;
        return "tag";
      }
    }
    else if (ch == "&") {
      stream.eatWhile(/[^;]/);
      stream.eat(";");
      return "atom";
    }
    else {
      stream.eatWhile(/[^&<]/);
      return null;
    }
  }

  function inTag(stream, state) {
    var ch = stream.next();
    if (ch == ">" || (ch == "/" && stream.eat(">"))) {
      state.tokenize = inText;
      type = ch == ">" ? "endTag" : "selfcloseTag";
      return "tag";
    }
    else if (ch == "=") {
      type = "equals";
      return null;
    }
    else if (/[\'\"]/.test(ch)) {
      state.tokenize = inAttribute(ch);
      return state.tokenize(stream, state);
    }
    else {
      stream.eatWhile(/[^\s\u00a0=<>\"\'\/?]/);
      return "word";
    }
  }

  function inAttribute(quote) {
    return function(stream, state) {
      while (!stream.eol()) {
        if (stream.next() == quote) {
          state.tokenize = inTag;
          break;
        }
      }
      return "string";
    };
  }

  function inBlock(style, terminator) {
    return function(stream, state) {
      while (!stream.eol()) {
        if (stream.match(terminator)) {
          state.tokenize = inText;
          break;
        }
        stream.next();
      }
      return style;
    };
  }

  var curState, setStyle;
  function pass() {
    for (var i = arguments.length - 1; i >= 0; i--) curState.cc.push(arguments[i]);
  }
  function cont() {
    pass.apply(null, arguments);
    return true;
  }

  function pushContext(tagName, startOfLine) {
    var noIndent = Kludges.doNotIndent.hasOwnProperty(tagName) || (curState.context && curState.context.noIndent);
    curState.context = {
      prev: curState.context,
      tagName: tagName,
      indent: curState.indented,
      startOfLine: startOfLine,
      noIndent: noIndent
    };
  }
  function popContext() {
    if (curState.context) curState.context = curState.context.prev;
  }

  function element(type) {
    if (type == "openTag") {curState.tagName = tagName; return cont(attributes, endtag(curState.startOfLine));}
    else if (type == "closeTag") {
      var err = false;
      if (curState.context) {
        err = curState.context.tagName != tagName;
        popContext();
      } else {
        err = true;
      }
      if (err) setStyle = "error";
      return cont(endclosetag(err));
    }
    else if (type == "string") {
      if (!curState.context || curState.context.name != "!cdata") pushContext("!cdata");
      if (curState.tokenize == inText) popContext();
      return cont();
    }
    else return cont();
  }
  function endtag(startOfLine) {
    return function(type) {
      if (type == "selfcloseTag" ||
          (type == "endTag" && Kludges.autoSelfClosers.hasOwnProperty(curState.tagName.toLowerCase())))
        return cont();
      if (type == "endTag") {pushContext(curState.tagName, startOfLine); return cont();}
      return cont();
    };
  }
  function endclosetag(err) {
    return function(type) {
      if (err) setStyle = "error";
      if (type == "endTag") return cont();
      return pass();
    }
  }

  function attributes(type) {
    if (type == "word") {setStyle = "attribute"; return cont(attributes);}
    if (type == "equals") return cont(attvalue, attributes);
    return pass();
  }
  function attvalue(type) {
    if (type == "word" && Kludges.allowUnquoted) {setStyle = "string"; return cont();}
    if (type == "string") return cont(attvaluemaybe);
    return pass();
  }
  function attvaluemaybe(type) {
    if (type == "string") return cont(attvaluemaybe);
    else return pass();
  }

  return {
    startState: function() {
      return {tokenize: inText, cc: [], indented: 0, startOfLine: true, tagName: null, context: null};
    },

    token: function(stream, state) {
      if (stream.sol()) {
        state.startOfLine = true;
        state.indented = stream.indentation();
      }
      if (stream.eatSpace()) return null;

      setStyle = type = tagName = null;
      var style = state.tokenize(stream, state);
      if ((style || type) && style != "comment") {
        curState = state;
        while (true) {
          var comb = state.cc.pop() || element;
          if (comb(type || style)) break;
        }
      }
      state.startOfLine = false;
      return setStyle || style;
    },

    indent: function(state, textAfter) {
      var context = state.context;
      if (context && context.noIndent) return 0;
      if (alignCDATA && /<!\[CDATA\[/.test(textAfter)) return 0;
      if (context && /^<\//.test(textAfter))
        context = context.prev;
      while (context && !context.startOfLine)
        context = context.prev;
      if (context) return context.indent + indentUnit;
      else return 0;
    },

    compareStates: function(a, b) {
      if (a.indented != b.indented || a.tagName != b.tagName) return false;
      for (var ca = a.context, cb = b.context; ; ca = ca.prev, cb = cb.prev) {
        if (!ca || !cb) return ca == cb;
        if (ca.tagName != cb.tagName) return false;
      }
    },

    electricChars: "/"
  };
});

CodeMirror.defineMIME("application/xml", "xml");
CodeMirror.defineMIME("text/html", {name: "xml", htmlMode: true});

// mode/php/php.js

(function() {
  function keywords(str) {
    var obj = {}, words = str.split(" ");
    for (var i = 0; i < words.length; ++i) obj[words[i]] = true;
    return obj;
  }
  function heredoc(delim) {
    return function(stream, state) {
      if (stream.match(delim)) state.tokenize = null;
      else stream.skipToEnd();
      return "string";
    }
  }
  var phpConfig = {
    name: "clike",
    keywords: keywords("abstract and array as break case catch cfunction class clone const continue declare " +
                       "default do else elseif enddeclare endfor endforeach endif endswitch endwhile extends " +
                       "final for foreach function global goto if implements interface instanceof namespace " +
                       "new or private protected public static switch throw try use var while xor return"),
    blockKeywords: keywords("catch do else elseif for foreach if switch try while"),
    atoms: keywords("true false null"),
    multiLineStrings: true,
    hooks: {
      "$": function(stream, state) {
        stream.eatWhile(/[\w\$_]/);
        return "variable-2";
      },
      "<": function(stream, state) {
        if (stream.match(/<</)) {
          stream.eatWhile(/[\w\.]/);
          state.tokenize = heredoc(stream.current().slice(3));
          return state.tokenize(stream, state);
        }
        return false;
      }
    }
  };

  CodeMirror.defineMode("php", function(config, parserConfig) {
    var htmlMode = CodeMirror.getMode(config, "text/html");
    var jsMode = CodeMirror.getMode(config, "text/javascript");
    var cssMode = CodeMirror.getMode(config, "text/css");
    var phpMode = CodeMirror.getMode(config, phpConfig);

    function dispatch(stream, state) { // TODO open PHP inside text/css
      if (state.curMode == htmlMode) {
        var style = htmlMode.token(stream, state.curState);
        if (style == "meta" && /^<\?/.test(stream.current())) {
          state.curMode = phpMode;
          state.curState = state.php;
          state.curClose = /^\?>/;
		  state.mode =  'php';
        }
        else if (style == "tag" && stream.current() == ">" && state.curState.context) {
          if (/^script$/i.test(state.curState.context.tagName)) {
            state.curMode = jsMode;
            state.curState = jsMode.startState(htmlMode.indent(state.curState, ""));
            state.curClose = /^<\/\s*script\s*>/i;
			state.mode =  'javascript';
          }
          else if (/^style$/i.test(state.curState.context.tagName)) {
            state.curMode = cssMode;
            state.curState = cssMode.startState(htmlMode.indent(state.curState, ""));
            state.curClose =  /^<\/\s*style\s*>/i;
            state.mode =  'css';
          }
        }
        return style;
      }
      else if (stream.match(state.curClose, false)) {
        state.curMode = htmlMode;
        state.curState = state.html;
        state.curClose = null;
		state.mode =  'html';
        return dispatch(stream, state);
      }
      else return state.curMode.token(stream, state.curState);
    }

    return {
      startState: function() {
        var html = htmlMode.startState();
        return {html: html,
                php: phpMode.startState(),
                curMode:	parserConfig.startOpen ? phpMode : htmlMode,
                curState:	parserConfig.startOpen ? phpMode.startState() : html,
                curClose:	parserConfig.startOpen ? /^\?>/ : null,
				mode:		parserConfig.startOpen ? 'php' : 'html'}
      },

      copyState: function(state) {
        var html = state.html, htmlNew = CodeMirror.copyState(htmlMode, html),
            php = state.php, phpNew = CodeMirror.copyState(phpMode, php), cur;
        if (state.curState == html) cur = htmlNew;
        else if (state.curState == php) cur = phpNew;
        else cur = CodeMirror.copyState(state.curMode, state.curState);
        return {html: htmlNew, php: phpNew, curMode: state.curMode, curState: cur, curClose: state.curClose};
      },

      token: dispatch,

      indent: function(state, textAfter) {
        if ((state.curMode != phpMode && /^\s*<\//.test(textAfter)) ||
            (state.curMode == phpMode && /^\?>/.test(textAfter)))
          return htmlMode.indent(state.html, textAfter);
        return state.curMode.indent(state.curState, textAfter);
      },

      electricChars: "/{}:"
    }
  });
  CodeMirror.defineMIME("application/x-httpd-php", "php");
  CodeMirror.defineMIME("application/x-httpd-php-open", {name: "php", startOpen: true});
  CodeMirror.defineMIME("text/x-php", phpConfig);
})();

// mode/plsql/plsql.js

CodeMirror.defineMode("plsql", function(config, parserConfig) {
  var indentUnit       = config.indentUnit,
      keywords         = parserConfig.keywords,
      functions        = parserConfig.functions,
      types            = parserConfig.types,
      sqlplus          = parserConfig.sqlplus,
      multiLineStrings = parserConfig.multiLineStrings;
  var isOperatorChar   = /[+\-*&%=<>!?:\/|]/;
  function chain(stream, state, f) {
    state.tokenize = f;
    return f(stream, state);
  }

  var type;
  function ret(tp, style) {
    type = tp;
    return style;
  }

  function tokenBase(stream, state) {
    var ch = stream.next();
    // start of string?
    if (ch == '"' || ch == "'")
      return chain(stream, state, tokenString(ch));
    // is it one of the special signs []{}().,;? Seperator?
    else if (/[\[\]{}\(\),;\.]/.test(ch))
      return ret(ch);
    // start of a number value?
    else if (/\d/.test(ch)) {
      stream.eatWhile(/[\w\.]/);
      return ret("number", "number");
    }
    // multi line comment or simple operator?
    else if (ch == "/") {
      if (stream.eat("*")) {
        return chain(stream, state, tokenComment);
      }
      else {
        stream.eatWhile(isOperatorChar);
        return ret("operator", "operator");
      }
    }
    // single line comment or simple operator?
    else if (ch == "-") {
      if (stream.eat("-")) {
        stream.skipToEnd();
        return ret("comment", "comment");
      }
      else {
        stream.eatWhile(isOperatorChar);
        return ret("operator", "operator");
      }
    }
    // pl/sql variable?
    else if (ch == "@" || ch == "$") {
      stream.eatWhile(/[\w\d\$_]/);
      return ret("word", "variable");
    }
    // is it a operator?
    else if (isOperatorChar.test(ch)) {
      stream.eatWhile(isOperatorChar);
      return ret("operator", "operator");
    }
    else {
      // get the whole word
      stream.eatWhile(/[\w\$_]/);
      // is it one of the listed keywords?
      if (keywords && keywords.propertyIsEnumerable(stream.current().toLowerCase())) return ret("keyword", "keyword");
      // is it one of the listed functions?
      if (functions && functions.propertyIsEnumerable(stream.current().toLowerCase())) return ret("keyword", "builtin");
      // is it one of the listed types?
      if (types && types.propertyIsEnumerable(stream.current().toLowerCase())) return ret("keyword", "variable-2");
      // is it one of the listed sqlplus keywords?
      if (sqlplus && sqlplus.propertyIsEnumerable(stream.current().toLowerCase())) return ret("keyword", "variable-3");
      // default: just a "word"
      return ret("word", "plsql-word");
    }
  }

  function tokenString(quote) {
    return function(stream, state) {
      var escaped = false, next, end = false;
      while ((next = stream.next()) != null) {
        if (next == quote && !escaped) {end = true; break;}
        escaped = !escaped && next == "\\";
      }
      if (end || !(escaped || multiLineStrings))
        state.tokenize = tokenBase;
      return ret("string", "plsql-string");
    };
  }

  function tokenComment(stream, state) {
    var maybeEnd = false, ch;
    while (ch = stream.next()) {
      if (ch == "/" && maybeEnd) {
        state.tokenize = tokenBase;
        break;
      }
      maybeEnd = (ch == "*");
    }
    return ret("comment", "plsql-comment");
  }

  // Interface

  return {
    startState: function(basecolumn) {
      return {
        tokenize: tokenBase,
        startOfLine: true
      };
    },

    token: function(stream, state) {
      if (stream.eatSpace()) return null;
      var style = state.tokenize(stream, state);
      return style;
    }
  };
});

(function() {
  function keywords(str) {
    var obj = {}, words = str.split(" ");
    for (var i = 0; i < words.length; ++i) obj[words[i]] = true;
    return obj;
  }
  var cKeywords = "abort accept access add all alter and any array arraylen as asc assert assign at attributes audit " +
        "authorization avg " +
        "base_table begin between binary_integer body boolean by " +
        "case cast char char_base check close cluster clusters colauth column comment commit compress connect " +
        "connected constant constraint crash create current currval cursor " +
        "data_base database date dba deallocate debugoff debugon decimal declare default definition delay delete " +
        "desc digits dispose distinct do drop " +
        "else elsif enable end entry escape exception exception_init exchange exclusive exists exit external " +
        "fast fetch file for force form from function " +
        "generic goto grant group " +
        "having " +
        "identified if immediate in increment index indexes indicator initial initrans insert interface intersect " +
        "into is " +
        "key " +
        "level library like limited local lock log logging long loop " +
        "master maxextents maxtrans member minextents minus mislabel mode modify multiset " +
        "new next no noaudit nocompress nologging noparallel not nowait number_base " +
        "object of off offline on online only open option or order out " +
        "package parallel partition pctfree pctincrease pctused pls_integer positive positiven pragma primary prior " +
        "private privileges procedure public " +
        "raise range raw read rebuild record ref references refresh release rename replace resource restrict return " +
        "returning reverse revoke rollback row rowid rowlabel rownum rows run " +
        "savepoint schema segment select separate session set share snapshot some space split sql start statement " +
        "storage subtype successful synonym " +
        "tabauth table tables tablespace task terminate then to trigger truncate type " +
        "union unique unlimited unrecoverable unusable update use using " +
        "validate value values variable view views " +
        "when whenever where while with work";

  var cFunctions = "abs acos add_months ascii asin atan atan2 average " +
        "bfilename " +
        "ceil chartorowid chr concat convert cos cosh count " +
        "decode deref dual dump dup_val_on_index " +
        "empty error exp " +
        "false floor found " +
        "glb greatest " +
        "hextoraw " +
        "initcap instr instrb isopen " +
        "last_day least lenght lenghtb ln lower lpad ltrim lub " +
        "make_ref max min mod months_between " +
        "new_time next_day nextval nls_charset_decl_len nls_charset_id nls_charset_name nls_initcap nls_lower " +
        "nls_sort nls_upper nlssort no_data_found notfound null nvl " +
        "others " +
        "power " +
        "rawtohex reftohex round rowcount rowidtochar rpad rtrim " +
        "sign sin sinh soundex sqlcode sqlerrm sqrt stddev substr substrb sum sysdate " +
        "tan tanh to_char to_date to_label to_multi_byte to_number to_single_byte translate true trunc " +
        "uid upper user userenv " +
        "variance vsize";

  var cTypes = "bfile blob " +
        "character clob " +
        "dec " +
        "float " +
        "int integer " +
        "mlslabel " +
        "natural naturaln nchar nclob number numeric nvarchar2 " +
        "real rowtype " +
        "signtype smallint string " +
        "varchar varchar2";

  var cSqlplus = "appinfo arraysize autocommit autoprint autorecovery autotrace " +
        "blockterminator break btitle " +
        "cmdsep colsep compatibility compute concat copycommit copytypecheck " +
        "define describe " +
        "echo editfile embedded escape exec execute " +
        "feedback flagger flush " +
        "heading headsep " +
        "instance " +
        "linesize lno loboffset logsource long longchunksize " +
        "markup " +
        "native newpage numformat numwidth " +
        "pagesize pause pno " +
        "recsep recsepchar release repfooter repheader " +
        "serveroutput shiftinout show showmode size spool sqlblanklines sqlcase sqlcode sqlcontinue sqlnumber " +
        "sqlpluscompatibility sqlprefix sqlprompt sqlterminator suffix " +
        "tab term termout time timing trimout trimspool ttitle " +
        "underline " +
        "verify version " +
        "wrap";

  CodeMirror.defineMIME("text/x-plsql", {
    name: "plsql",
    keywords: keywords(cKeywords),
    functions: keywords(cFunctions),
    types: keywords(cTypes),
    sqlplus: keywords(cSqlplus)
  });
}());
