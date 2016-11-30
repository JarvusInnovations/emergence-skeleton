document.emr = {
    git: {
        repo: {
            selectors: {
                'repo': '#js-repo',
                'pushButton': '#js-push',
                'pullButton': '#js-pull',
                'cleanButton': '#js-clean',
                'resetToRemoteHeadButton': '#js-reset-remote-head',
                'toDiskButton': '#js-to-disk',
                'fromDiskButton': '#js-from-disk',
                'selectAllButton': '#js-select-all',
                'stageSelectedButton': '#js-stage-selected',
                'commitButton': '#js-commit',
                
                'stageFileButtons': '.js-stage-file',
                'resetFileButtons': '.js-reset-file',
                'diffRemoteHeadButtons': '.js-diff-remote-head',
                'diffLocalHeadButtons': '.js-diff-local-head',
                'diffBehindHeadButtons': '.js-diff-behind-head',
                
                'activeTabFileCheckboxes': 'div.active div.emr-git-files input[type=checkbox]',
                
                'upperStatus': '#js-upper-status',
                
                'alertSuccessClass': 'alert-success',
                'alertErrorClass': 'alert-danger'
            },
            repo: null,
            /*
                Run this document.onready()
            */
            bind: function() {
                this.repo = $(this.selectors.repo).val();
                
                // dropdowns
                $('.dropdown-toggle').dropdown();
                
                $(this.selectors.pullButton).on('click', this.pull.bind(this));
                $(this.selectors.pushButton).on('click', this.push.bind(this));
                $(this.selectors.cleanButton).on('click', this.clean.bind(this));
                $(this.selectors.resetToRemoteHeadButton).on('click', this.resetToRemoteHead.bind(this));
                $(this.selectors.toDiskButton).on('click', this.vfstodisk.bind(this));
                $(this.selectors.fromDiskButton).on('click', this.disktovfs.bind(this));
                $(this.selectors.commitButton).on('click', this.commit.bind(this));
                
                $(this.selectors.selectAllButton).on('click', this.selectAll.bind(this));
                $(this.selectors.stageSelectedButton).on('click', this.stageSelected.bind(this));
                
                $(this.selectors.stageFileButtons).on('click', this.stageFile.bind(this));
                $(this.selectors.resetFileButtons).on('click', this.resetFile.bind(this));
                //$(this.selectors.diffRemoteHeadButtons).on('click', this.diffRemoteHead.bind(this));
                //$(this.selectors.diffLocalHeadButtons).on('click', this.diffLocalHead.bind(this));
                //$(this.selectors.diffBehindHeadButtons).on('click', this.diffBehindHead.bind(this));
            },
            vfstodisk: function() {
                var prompt = 'Are you sure you want to push file system changes from the VFS to this Git repository?<br><br>'
                             + 'This may overwrite any uncommited changes in the working copy and they would be lost permanently.<br><br>'
                             + 'This status page will automatically refresh after the operation has completed.';
                bootbox.confirm({
                    message: prompt,
                    title: 'VFS &rarr; Disk',
                    callback: function(result) {
                        if(result) {
                            $.ajax(document.location.pathname + '/sync/to-disk', {
                                method: 'post',
                                dataType: 'json',
                                success: function(data,status,xhr) {
                                    if(data.success) {
                                        document.location.reload();
                                    } else {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html('Connection failed.');
                                        domStatus.addClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    }
                                }.bind(this),
                                error: function(data,status,xhr) {
                                    var domStatus = $(this.selectors.upperStatus);
                                    domStatus.html(data.error);
                                    domStatus.addClass(this.selectors.alertErrorClass);
                                    domStatus.removeClass(this.selectors.alertSuccessClass);
                                    domStatus.removeClass('hidden');
                                    domStatus.show();
                                }.bind(this)
                            });
                        }
                    }.bind(this)
                })
            },
            disktovfs: function() {
                var prompt = 'Are you sure you want to pull the contents of the Git repository into the VFS?<br><br>'
                             + 'This status page will automatically refresh after the operation has completed.';
                bootbox.confirm({
                    message: prompt,
                    title: 'Disk &rarr; VFS',
                    callback: function(result) {
                        if(result) {
                            $.ajax(document.location.pathname + '/sync/from-disk', {
                                method: 'post',
                                dataType: 'json',
                                success: function(data,status,xhr) {
                                    if(data.success) {
                                        document.location.reload();
                                    } else {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html('Connection failed.');
                                        domStatus.addClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    }
                                }.bind(this),
                                error: function(data,status,xhr) {
                                    var domStatus = $(this.selectors.upperStatus);
                                    domStatus.html(data.error);
                                    domStatus.addClass(this.selectors.alertErrorClass);
                                    domStatus.removeClass(this.selectors.alertSuccessClass);
                                    domStatus.removeClass('hidden');
                                    domStatus.show();
                                }.bind(this)
                            });
                        }
                    }.bind(this)
                });
            },
            resetToRemoteHead: function(e) {
                e.preventDefault();
                
                var prompt = 'Are you sure you want to reset the Git repository to the remote HEAD commit?<br>';
                bootbox.confirm({
                    message: prompt,
                    title: 'Reset to Remote HEAD',
                    callback: function(result) {
                        if(result) {
                            $.ajax(document.location.pathname + '/hard-reset-remote-head', {
                                method: 'post',
                                dataType: 'json',
                                success: function(data,status,xhr) {
                                    if(data.success) {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html(data.output);
                                        domStatus.addClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    } else {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html('Connection failed.');
                                        domStatus.addClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    }
                                }.bind(this),
                                error: function(data,status,xhr) {
                                    var domStatus = $(this.selectors.upperStatus);
                                    domStatus.html(data.error);
                                    domStatus.addClass(this.selectors.alertErrorClass);
                                    domStatus.removeClass(this.selectors.alertSuccessClass);
                                    domStatus.removeClass('hidden');
                                    domStatus.show();
                                }.bind(this)
                            });
                        }
                    }.bind(this)
                });
            },
            clean: function(e) {
                e.preventDefault();
                
                var prompt = 'Are you sure you want to clean the Git repository?<br>'
                            + 'Any untracked files or directories will be permanently deleted.<br>';
                bootbox.confirm({
                    message: prompt,
                    title: 'Clean',
                    callback: function(result) {
                        if(result) {
                            $.ajax(document.location.pathname + '/clean', {
                                method: 'post',
                                dataType: 'json',
                                success: function(data,status,xhr) {
                                    if(data.success) {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html(nl2br(data.output));
                                        domStatus.addClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    } else {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html('Connection failed.');
                                        domStatus.addClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    }
                                }.bind(this),
                                error: function(data,status,xhr) {
                                    var domStatus = $(this.selectors.upperStatus);
                                    domStatus.html(data.error);
                                    domStatus.addClass(this.selectors.alertErrorClass);
                                    domStatus.removeClass(this.selectors.alertSuccessClass);
                                    domStatus.removeClass('hidden');
                                    domStatus.show();
                                }.bind(this)
                            });
                        }
                    }.bind(this)
                });
            },
            pull: function() {
                var prompt = 'Are you sure you want to pull the Git repository from remote?<br>';
                bootbox.confirm({
                    message: prompt,
                    title: 'Pull',
                    callback: function(result) {
                        if(result) {
                            $.ajax(document.location.pathname + '/pull', {
                                method: 'post',
                                dataType: 'json',
                                success: function(data,status,xhr) {
                                    if(data.success) {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html(data.output);
                                        domStatus.addClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    } else {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html('Connection failed.');
                                        domStatus.addClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    }
                                }.bind(this),
                                error: function(data,status,xhr) {
                                    var domStatus = $(this.selectors.upperStatus);
                                    domStatus.html(data.error);
                                    domStatus.addClass(this.selectors.alertErrorClass);
                                    domStatus.removeClass(this.selectors.alertSuccessClass);
                                    domStatus.removeClass('hidden');
                                    domStatus.show();
                                }.bind(this)
                            });
                        }
                    }.bind(this)
                });
            },
            push: function() {
                var prompt = 'Are you sure you want to push the Git repository to remote?<br>';
                bootbox.confirm({
                    message: prompt,
                    title: 'Push',
                    callback: function(result) {
                        if(result) {
                            $.ajax(document.location.pathname + '/push', {
                                method: 'post',
                                dataType: 'json',
                                success: function(data,status,xhr) {
                                    if(data.success) {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html(data.output);
                                        domStatus.addClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    } else {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html(data.error);
                                        domStatus.addClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    }
                                }.bind(this),
                                error: function(data,status,xhr) {
                                    var domStatus = $(this.selectors.upperStatus);
                                    domStatus.html('Connection failed.');
                                    domStatus.addClass(this.selectors.alertErrorClass);
                                    domStatus.removeClass(this.selectors.alertSuccessClass);
                                    domStatus.removeClass('hidden');
                                    domStatus.show();
                                }.bind(this)
                            });
                        }
                    }.bind(this)
                });
            },
            selectAll: function(e) {
                $(this.selectors.activeTabFileCheckboxes).prop('checked', true);
            },
            stageSelected: function(e) {
                var checked = $(this.selectors.activeTabFileCheckboxes+':checked');
                
                var files = [];
                
                $.each(checked,function(index,checkbox){
                    files.push($(checkbox).val());
                }.bind(this));
                
                if(!files.length) {
                    bootbox.alert('Please make a selection first.');
                }
                else {
                    var prompt = 'Are you sure you want to stage ' + files.length + ' selected path' + (files.length>1?'s':'') + '?<br>'
                            
                    bootbox.confirm({
                        message: prompt,
                        title: 'Stage',
                        callback: function(result) {
                            if(result) {
                                $.ajax(document.location.pathname + '/stage-multi', {
                                    method: 'post',
                                    dataType: 'json',
                                    contentType: 'application/json; charset=utf-8',
                                    data: JSON.stringify({Files:files}),
                                    success: function(data,status,xhr) {
                                        if(data.success) {
                                            document.location.reload();
                                        } else {
                                            var domStatus = $(this.selectors.upperStatus);
                                            domStatus.html('Connection failed.');
                                            domStatus.addClass(this.selectors.alertErrorClass);
                                            domStatus.removeClass(this.selectors.alertSuccessClass);
                                            domStatus.removeClass('hidden');
                                            domStatus.show();
                                        }
                                    }.bind(this),
                                    error: function(data,status,xhr) {
                                        var domStatus = $(this.selectors.upperStatus);
                                        
                                        var errors = '';
                                        
                                        $.each(data.operations, function(index, item) {
                                            errors += item.output + '<br>';
                                        }.bind(this));
                                        domStatus.html(errors);
                                        domStatus.addClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    }.bind(this)
                                });
                            }
                        }.bind(this)
                    });
                }
            },
            stageFile: function(e) {
                var path = $(e.toElement).closest('div.btn-group').attr('data-file');
                
                var prompt = 'Are you sure you want to stage this path?<br><br>'
                            + path;
                            
                bootbox.confirm({
                    message: prompt,
                    title: 'Stage',
                    callback: function(result) {
                        if(result) {
                            $.ajax(document.location.pathname + '/stage', {
                                method: 'post',
                                dataType: 'json',
                                contentType: 'application/json; charset=utf-8',
                                data: JSON.stringify({File:path}),
                                success: function(data,status,xhr) {
                                    if(data.success) {
                                        document.location.reload();
                                    } else {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html('Connection failed.');
                                        domStatus.addClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    }
                                }.bind(this),
                                error: function(data,status,xhr) {
                                    var domStatus = $(this.selectors.upperStatus);
                                    
                                    var errors = '';
                                    
                                    $.each(data.operations, function(index, item) {
                                        errors += item.output + '<br>';
                                    }.bind(this));
                                    domStatus.html(errors);
                                    domStatus.addClass(this.selectors.alertErrorClass);
                                    domStatus.removeClass(this.selectors.alertSuccessClass);
                                    domStatus.removeClass('hidden');
                                    domStatus.show();
                                }.bind(this)
                            });
                        }
                    }.bind(this)
                });
            },
            resetFile: function(e) {
                var path = $(e.toElement).closest('div.btn-group').attr('data-file');
                
                var prompt = 'Are you sure you want to reset this path?<br><br>'
                            + path;
                            
                bootbox.confirm({
                    message: prompt,
                    title: 'Reset',
                    callback: function(result) {
                        if(result) {
                            $.ajax(document.location.pathname + '/reset', {
                                method: 'post',
                                dataType: 'json',
                                contentType: 'application/json; charset=utf-8',
                                data: JSON.stringify({File:path}),
                                success: function(data,status,xhr) {
                                    if(data.success) {
                                        document.location.reload();
                                    } else {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html('Connection failed.');
                                        domStatus.addClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    }
                                }.bind(this),
                                error: function(data,status,xhr) {
                                    var domStatus = $(this.selectors.upperStatus);
                                    
                                    var errors = '';
                                    
                                    $.each(data.operations, function(index, item) {
                                        errors += item.output + '<br>';
                                    }.bind(this));
                                    domStatus.html(errors);
                                    domStatus.addClass(this.selectors.alertErrorClass);
                                    domStatus.removeClass(this.selectors.alertSuccessClass);
                                    domStatus.removeClass('hidden');
                                    domStatus.show();
                                }.bind(this)
                            });
                        }
                    }.bind(this)
                });
            },
            diffRemoteHead: function(e) {
                e.preventDefault();
                
                var path = $(e.toElement).closest('div.btn-group').attr('data-file');
            },
            diffLocalHead: function(e) {
                e.preventDefault();
                
                var path = $(e.toElement).closest('div.btn-group').attr('data-file');
            },
            diffBehindHead: function(e) {
                e.preventDefault();
                
                var path = $(e.toElement).closest('div.btn-group').attr('data-file');
                
                // get amount behind from
                // <a data-behind="{$i}">
            },
            commit: function(e) {
                e.preventDefault();
                
                var data = {
                    author: $('input[name=author]').val(),
                    subject: $('input[name=subject]').val(),
                    description: $('textarea[name=description]').val()
                };
                
                var prompt = 'Are you sure you want to commit?<br>';
                bootbox.confirm({
                    message: prompt,
                    title: 'Commit',
                    callback: function(result) {
                        if(result) {
                            $.ajax(document.location.pathname + '/commit', {
                                method: 'post',
                                dataType: 'json',
                                contentType: 'application/json; charset=utf-8',
                                data: JSON.stringify(data),
                                success: function(data,status,xhr) {
                                    if(data.success) {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html(nl2br(data.output));
                                        domStatus.addClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    } else {
                                        var domStatus = $(this.selectors.upperStatus);
                                        domStatus.html(data.error);
                                        domStatus.addClass(this.selectors.alertErrorClass);
                                        domStatus.removeClass(this.selectors.alertSuccessClass);
                                        domStatus.removeClass('hidden');
                                        domStatus.show();
                                    }
                                }.bind(this),
                                error: function(data,status,xhr) {
                                    var domStatus = $(this.selectors.upperStatus);
                                    domStatus.html(data.responseJSON.message);
                                    domStatus.addClass(this.selectors.alertErrorClass);
                                    domStatus.removeClass(this.selectors.alertSuccessClass);
                                    domStatus.removeClass('hidden');
                                    domStatus.show();
                                }.bind(this)
                            });
                        }
                    }.bind(this)
                });
            }
        }
    }
};

function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

$(document).ready(function() {
    document.emr.git.repo.bind();
})