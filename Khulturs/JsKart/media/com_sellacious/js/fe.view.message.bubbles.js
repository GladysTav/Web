/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
var messageBubbles = function () {
    this.options = {
        chat_container: '',
        poll_interval_active: 2000,
        poll_interval_inactive: 5000,
        total_messages: 0
    };
    this.base = '';
    this.token = '';
    this.poll = '';
    this.thread_id = 0;
    this.first_message_id = 0;
    this.last_message_id = 0;
    this.poll_interval = 2000;
    this.shiftDown = false;
    this.sendingMessage = false;

    // Whether there are any 'previous' messages to load
    this.moreMessages = true;
};

jQuery(function ($) {
    messageBubbles.prototype = {
        setup: function (options) {
            $.extend(this.options, options);
            var that = this;

            var paths = Joomla.getOptions('system.paths', {});
            that.base = paths.root || '';

            that.token = Joomla.getOptions('csrf.token');

            let chat_container = $(that.options.chat_container);
            that.thread_id = chat_container.data('thread-id');
            that.first_message_id = chat_container.data('first-message-id');
            that.last_message_id = chat_container.data('last-message-id');

            let thread_tab = '#tab_' + that.thread_id + '-tab';
            let messageBox = $('#message-to-send-' + that.thread_id);

            that.poll_interval = $(thread_tab).hasClass('ctech-active') ? that.options.poll_interval_active : that.options.poll_interval_inactive;

            $(document).on('click', '.btn-send-' + that.thread_id, function(e) {
                e.preventDefault();
                if (messageBox.val() != '') that.sendMessage();
                else $('.btn-send-' + that.thread_id).attr('disabled', true)
            });

            $(document).on('shown.ctech-bs.tab', thread_tab, function (e) {
                if (!$(this).find('.message-count').hasClass('ctech-d-none')) that.readThread();

                that.scrollToMessage();
            });

            $(that.options.chat_container).scroll(function (e) {
                var container = $(this);

                // No previous messages available, so need to check for them again.
                if (!that.moreMessages) {
                    container.find('.no-more-messages').show();
                    return;
                };

                var messageList = container.find('ul');
                var pos = container.scrollTop();

                if (pos == 0) {
                    // Reveal old messages when reached top of the thread
                    var $current_top_element = messageList.children().first();
                    var previous_height = 0;

                    that.getPreviousMessages(function(response){
                        $current_top_element.prevAll().each(function() {
                            previous_height += $(this).outerHeight();
                        });

                        container.scrollTop(previous_height);

                        // Shift the loader at the top again
                        container.find('.loading-messages, .no-more-messages').prependTo(chat_container.find('ul'));
                    });
                }
            });

            // Keyboard events
            $(document).on('keypress', messageBox, function (e) {
                if(e.keyCode == 13) {
                    if(messageBox.is(":focus") && !that.shiftDown) {
                        e.preventDefault(); // prevent another \n from being entered
                        if (messageBox.val() != '') that.sendMessage()
                    }
                }
            });

            $(document).on('keydown', messageBox, function (e) {
                if(e.keyCode == 16) that.shiftDown = true;
            });

            $(document).on('keyup', messageBox, function (e) {
                if(e.keyCode == 16) that.shiftDown = false;
            });

            $(document).on('keyup', messageBox, function () {
               that.checkMessageBox();
            });

            that.checkMessageBox();
            that.scrollToMessage();
            that.pollMessages();
        },
        scrollToMessage: function (chat_container) {
            if (chat_container == null) {
                chat_container = $(this.options.chat_container);
            }

            chat_container.scrollTop(chat_container[0].scrollHeight);
        },
        checkMessageBox: function () {
            var value = $(this.options.message_box).val();

            if (value == '') {
                $('.btn-send-' + this.thread_id).attr('disabled', true);
            } else {
                $('.btn-send-' + this.thread_id).attr('disabled', false);
            }
        },
        pollMessages: function () {
            var that = this;

            let thread_tab = '#tab_' + that.thread_id + '-tab';

            that.poll = setTimeout(function () {
                var chat_container = $(that.options.chat_container);

                var data = {
                    'option': 'com_sellacious',
                    'task': 'messages.getMessagesAjax',
                    'format': 'json',
                    'thread_id': that.thread_id,
                    'last_message_id': that.last_message_id,
                };

                data[that.token] = 1;

                $.ajax({
                    url: that.base + '/index.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: data,
                }).done(function (response) {
                    if (response.success == true) {
                        if (response.data.thread != '') {
                            chat_container.find('ul').append(response.data.thread);

                            // Scroll to bottom only if there are new messages
                            that.scrollToMessage(chat_container);

                            if (response.data.last_message.length) {
                                $(`#tab_${threadId}-tab`).find('.message-last .last-message-body').html(response.data.last_message.body);
                                $(`#tab_${threadId}-tab`).find('.message-last .last-message-time').html(response.data.last_message.created);
                            }

                            if (response.data.last_message_id > 0) {
                                that.last_message_id = response.data.last_message_id;
                                chat_container.attr('data-last-message-id', response.data.last_message_id);
                            }
                        }

                        // Change poll interval according to active tab
                        if ($(thread_tab).hasClass('ctech-active')) {
                            that.poll_interval = that.options.poll_interval_active;

                            // If new message found in active tab, then mark it as read
                            if (!$(thread_tab).find('.message-count').hasClass('ctech-d-none')) {
                                that.readThread();
                            }
                        } else {
                            that.poll_interval = that.options.poll_interval_inactive;
                        }

                        that.pollMessages(chat_container);
                    } else {
                        Joomla.renderMessages({warning: [response.message]});
                    }
                }).fail(function (jqXHR) {
                    Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
                    console.log(jqXHR.responseText);
                });
            }, this.poll_interval);
        },
        getPreviousMessages: function(callback) {
            var that = this;
            var chat_container = $(that.options.chat_container);

            var data = {
                'option': 'com_sellacious',
                'format': 'json',
                'task': 'messages.getMessagesAjax',
                'thread_id': that.thread_id,
                'first_message_id': that.first_message_id,
            };

            data[that.token] = 1;

            $.ajax({
                url: that.base + '/index.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: data,
                beforeSend: function () {
                    chat_container.find('.loading-messages').show();
                },
                complete: function () {
                    chat_container.find('.loading-messages').hide();
                }
            }).done(function (response) {
                if (response.success == true) {
                    if (response.data.thread != '') {
                        chat_container.find('ul').prepend(response.data.thread);

                        if (response.data.first_message_id > 0) {
                            that.first_message_id = response.data.first_message_id;
                            chat_container.attr('data-first-message-id', response.data.first_message_id);
                        }

                        if (typeof callback === 'function') callback(response);
                    } else {
                        // No 'previous' messages available
                        chat_container.find('.no-more-messages').show();
                        that.moreMessages = false;
                    }
                } else {
                    Joomla.renderMessages({warning: [response.message]});
                }
            }).fail(function (jqXHR) {
                Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
                console.log(jqXHR.responseText);
            });
        },
        sendMessage: function () {
            if (this.sendingMessage) return;
            var that = this;
            var btn_send = '.btn-send-' + that.thread_id;
            var form = $(btn_send).closest('form');
            var data = form.serializeArray();

            let empty = false;

            data.forEach(dat => {
                if (dat.name == 'jform[body]') {
                    if (dat.name == '') empty = true;
                }
            });

            if (empty == true) return;

            data.push({name: 'option', value: 'com_sellacious'});
            data.push({name: 'format', value: 'json'});
            data.push({name: 'task', value: 'messages.sendMessageAjax'});

            $.ajax({
                url: that.base + '/index.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: data,
                beforeSend: function () {
                    $(btn_send).attr('disabled', true);
                    that.sendingMessage = true;
                    $(btn_send).text(Joomla.JText._('COM_SELLACIOUS_MESSAGE_SENDING_REPLY'));
                },
                complete: function () {
                    $(btn_send).attr('disabled', false);
                    that.sendingMessage = false;
                    $(btn_send).text(Joomla.JText._('COM_SELLACIOUS_MESSAGE_REPLY_TITLE'));
                }
            }).done(function (response) {
                if (response.success == true) {
                    clearTimeout(that.poll);

                    var parent_field = $('#chatForm' + that.thread_id).find('input[name="jform[parent_id]"]');
                    let old_thread_id = that.thread_id

                    if (parent_field.val() == 0) {
                        parent_field.val(response.data.message.id);

                        $('#message-to-send-' + old_thread_id).attr('id', `message-to-send-${response.data.message.id}`);
                        $(btn_send).toggleClass(`btn-send-${old_thread_id}, btn-send-${response.data.message.id}`)

                        that.thread_id = response.data.message.id;

                        $(`#tab_${old_thread_id}-tab`).attr('data-thread', that.thread_id).data('thread', that.thread_id).attr('id', `#tab_${that.thread_id}-tab`)
                            .find('.message-tab-details').attr('data-thread', that.thread_id).data('thread', that.thread_id)
                        $(that.options.chat_container).attr('data-thread-id', that.thread_id).data('thread-id', that.thread_id);
                    }

                    that.last_message_id = response.data.message.id;

                    response.data.bubbles.forEach(function(item){
                        var bubble = '<li>' + item + '</li>';
                        $(that.options.chat_container + ' ul').append(bubble);
                    });

                    that.scrollToMessage();
                    $('#message-to-send-' + that.thread_id).val('');

                    // Empty reference fields
                    form.find('input[name="jform[ref][context]"]').val('');
                    form.find('input[name="jform[ref][value]"]').val('');

                    that.pollMessages();
                } else {
                    Joomla.renderMessages({warning: [response.message]});
                }
            }).fail(function (jqXHR) {
                Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
                console.log(jqXHR.responseText);

            })
        },
        readThread: function () {
            var that = this;

            var data = {
                'option': 'com_sellacious',
                'format': 'json',
                'task': 'messages.readThreadAjax',
                'thread_id': that.thread_id,
            };

            data[that.token] = 1;

            $.ajax({
                url: that.base + '/index.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: data,
            }).done(function (response) {
                if (response.success == true) {
                    // Nothing to do
                } else {
                    Joomla.renderMessages({warning: [response.message]});
                }
            }).fail(function (jqXHR) {
                Joomla.renderMessages({warning: ['Request failed due to unknown error.']});
                console.log(jqXHR.responseText);
            });
        }
    };

    $(document).ready(function () {
        $('.chat-history').each(function () {
            var threadId = $(this).attr('data-thread-id');

            if (threadId != '') {
                var o = new messageBubbles;
                o.setup({
                    chat_container: '.chat-history-' + threadId
                });
            }
        });

        let getUnreadByThread = () => {
            let threadIds = [];
            $('.message-tab-details').each((i, tab) => {
                return threadIds.push($(tab).data('thread'));
            });

            const paths = Joomla.getOptions('system.paths', {});
            const base  = paths.root || '';
            const threadIdsS = JSON.stringify(threadIds);

            fetch(`${base}/index.php?option=com_sellacious&task=messages.getUnreadCountByThread&format=json&threadIds=${threadIdsS}`, {
                cache: 'no-cache',
                redirect: 'follow',
                referrer: 'no-referrer'
            })
                .then((response) => response.json())
                .then((response) => {
                    let latestMessage = {}
                    threadIds.forEach((id) => {
                        if (!Object.keys(latestMessage).length) latestMessage = response.data[id].last

                        if (latestMessage.date_sent < response.data[id].last.date_sent) {
                            $(`#tab_${id}-tab`).closest('.ctech-nav-item').prependTo('#messages_tabs')
                        }

                        $(`.message-tab-details[data-thread="${id}"]`).find('.last-message-body').html(response.data[id].last.body);
                        $(`.message-tab-details[data-thread="${id}"]`).find('.last-message-time').html(response.data[id].last.created);
                        if (response.data[id].unread > 0) {
                            $(`.message-tab-details[data-thread="${id}"]`).find('.message-count').html(response.data[id].unread).removeClass('ctech-d-none');
                        } else {
                            $(`.message-tab-details[data-thread="${id}"]`).find('.message-count').addClass('ctech-d-none');
                        }
                    })
                })
        };
        setInterval(getUnreadByThread, 3000);
    });
});
