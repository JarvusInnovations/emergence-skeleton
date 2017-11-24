# EmergenceEditor

## Getting started with development

- `sencha app build development`
- `sencha app refresh`

## TODO

### High priority

Editor isn't useful without these features:

- [X] Push all submodules
- [X] Route to already-open diff tabs
- [X] Wire toolbar
- [X] Resize ace editors
- [X] Wire revisions grid to diff views

### Medium priority

These should be completed before the editor is widely deployed:

- [X] Keep revisions navigated on diff tabs if paths same
- [X] Highlight/select current revision in revisions grid
- [X] Defer loading file content until tab is active
- [X] Eliminate store.DAVClient
- [X] Replace menu with toolbar
- [X] Push #activity path on activate
- [X] Support opening file at specific revision
- [X] Support opening file to specific line
- [ ] Fix file tree menus
- [ ] Test/restore rename file
- [ ] Test/restore delete file
- [ ] erase dead code

### Low priority

Editor can ship without these:

- [X] Select mode for diff view
- [ ] Implement file properties view
- [ ] Update revisions grid on save
- [ ] Highlight left diff revision in revisions grid
- [X] Fix fullscreen mode
- [ ] Update controller ref and control syntax
- [X] Try out ace searchbox extension -- windout find command being killed?
- [ ] Try out ace whitespace extension
- [ ] Replace icons with fontawesome
- [X] Restore / get rid of transfers log
- [ ] Try to remove images from builds by changing theme and adding post-build delete task to build.xml
- [ ] Minify ace / ace-diff
- [ ] Overwrite protection
- [X] Open and mask diff panel before content loads
- [ ] Get ace-diff using existing acepanels
- [ ] Enable comparing arbitrary rows from the revisions grid with multiselection?
- [ ] Stateful sharable editor config a-la bill clinton's class
- [ ] Cache revisions list for return to tab
- [ ] Track current revision loaded into editor, send on save
  - [ ] warn if revisions reloads and a newer revision exits
- [ ] Load full local+remote trees and cache them