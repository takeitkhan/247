/**
 * MM Referral Chat - Frontend Interface
 */

(function($) {
    'use strict';

    class MMChat {
        constructor() {
            this.currentConversation = null;
            this.conversations = [];
            this.pollingTimer = null;
            this.isOpen = false;
            this.currentTab = 'conversations'; // 'conversations' or 'partners'
            
            this.init();
        }

        init() {
            // Create DOM elements
            this.createChatUI();
            
            // Bind events
            this.bindEvents();
            
            // Load initial data
            this.loadConversations();
            
            // Start polling for new messages
            this.startPolling();
        }

        createChatUI() {
            const html = `
                <button class="mm-chat-toggle-btn" id="mm-chat-toggle" title="Open Chat">
                    💬
                    <span class="mm-chat-badge hidden" id="mm-chat-badge">0</span>
                </button>
                
                <div class="mm-chat-container hidden" id="mm-chat-container">
                    <!-- Header -->
                    <div class="mm-chat-header">
                        <div class="mm-chat-header-title">
                            <h3 id="mm-chat-title">Referral Chat</h3>
                        </div>
                        <button class="mm-chat-header-close" id="mm-chat-close">✕</button>
                    </div>

                    <!-- Tabs -->
                    <div style="display: flex; border-bottom: 1px solid #dee2e6;">
                        <button class="mm-chat-tab-btn active" data-tab="conversations" 
                                style="flex: 1; padding: 12px; border: none; background: white; cursor: pointer; font-weight: 500; color: #007bff; border-bottom: 2px solid #007bff;">
                            Conversations
                        </button>
                        <button class="mm-chat-tab-btn" data-tab="partners" 
                                style="flex: 1; padding: 12px; border: none; background: white; cursor: pointer; font-weight: 500; color: #6c757d;">
                            Add Chat
                        </button>
                    </div>

                    <!-- Content Area -->
                    <div class="mm-chat-content" style="flex: 1; overflow: hidden; display: flex; flex-direction: column;">
                        
                        <!-- Conversations Tab -->
                        <div class="mm-chat-tab-content active" data-tab="conversations" style="flex: 1; overflow: hidden; display: flex; flex-direction: column;">
                            <div class="mm-chat-conversations-list" style="flex: 1; overflow-y: auto; padding: 0;">
                                <!-- Loaded via JS -->
                            </div>
                        </div>

                        <!-- Partners Tab -->
                        <div class="mm-chat-tab-content" data-tab="partners" style="flex: 1; overflow: hidden; display: none; flex-direction: column;">
                            <div style="padding: 10px;">
                                <input type="text" class="mm-chat-partner-search" placeholder="Search referral partners..." 
                                       style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 6px; font-size: 14px;">
                            </div>
                            <div class="mm-chat-partners-list" style="flex: 1; overflow-y: auto; padding: 10px;">
                                <!-- Loaded via JS -->
                            </div>
                        </div>

                    </div>

                    <!-- Message Window (Hidden by default) -->
                    <div class="mm-chat-message-window" style="display: none; flex: 1; overflow: hidden; display: flex; flex-direction: column;">
                        <div class="mm-chat-back-header" style="padding: 12px; border-bottom: 1px solid #dee2e6; display: flex; gap: 10px; align-items: center;">
                            <button class="mm-chat-back-btn" style="background: none; border: none; font-size: 18px; cursor: pointer; padding: 0;">←</button>
                            <h3 class="mm-chat-other-user-name" style="margin: 0; font-size: 14px; flex: 1; font-weight: 600;"></h3>
                        </div>
                        <div class="mm-chat-messages" style="flex: 1; overflow-y: auto;"></div>
                        <div class="mm-chat-input-area">
                            <div class="mm-chat-input-wrapper">
                                <textarea class="mm-chat-input-field" placeholder="Type a message..." 
                                          style="resize: none;"></textarea>
                                <button class="mm-chat-send-btn" style="min-width: 36px;">
                                    📤
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(html);
        }

        bindEvents() {
            const self = this;

            // Toggle chat
            $('#mm-chat-toggle').on('click', function() {
                self.toggleChat();
            });

            // Close chat
            $('#mm-chat-close').on('click', function() {
                self.closeChat();
            });

            // Tab switching
            $('.mm-chat-tab-btn').on('click', function() {
                const tab = $(this).data('tab');
                self.switchTab(tab);
            });

            // Back button in message window
            $(document).on('click', '.mm-chat-back-btn', function() {
                self.closeConversation();
            });

            // Send message
            $(document).on('click', '.mm-chat-send-btn', function() {
                self.sendMessage();
            });

            // Enter to send (use keydown instead of keypress to avoid passive listener warnings)
            $(document).on('keydown', '.mm-chat-input-field', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                }
            });

            // Open conversation
            $(document).on('click', '.mm-conversation-item', function() {
                const convId = $(this).data('id');
                self.openConversation(convId);
            });

            // Open chat with partner
            $(document).on('click', '.mm-partner-item', function() {
                const userId = $(this).data('id');
                self.startConversation(userId);
            });

            // Search partners
            $(document).on('keyup', '.mm-chat-partner-search', function() {
                const searchTerm = $(this).val().toLowerCase();
                self.filterPartners(searchTerm);
            });
        }

        toggleChat() {
            if (this.isOpen) {
                this.closeChat();
            } else {
                this.openChat();
            }
        }

        openChat() {
            this.isOpen = true;
            $('#mm-chat-container').removeClass('hidden');
            $('#mm-chat-toggle').addClass('hidden');
            this.loadConversations();
        }

        closeChat() {
            this.isOpen = false;
            $('#mm-chat-container').addClass('hidden');
            $('#mm-chat-toggle').removeClass('hidden');
        }

        switchTab(tab) {
            const self = this;
            this.currentTab = tab;

            // Update active tab button
            $('.mm-chat-tab-btn').removeClass('active');
            $(`.mm-chat-tab-btn[data-tab="${tab}"]`).addClass('active');
            
            // Update styling
            $('.mm-chat-tab-btn').css({
                'color': '#6c757d',
                'border-bottom-color': 'transparent'
            });
            $(`.mm-chat-tab-btn[data-tab="${tab}"]`).css({
                'color': '#007bff',
                'border-bottom-color': '#007bff'
            });

            // Show/hide tab content
            $('.mm-chat-tab-content').each(function() {
                if ($(this).data('tab') === tab) {
                    $(this).show();
                    if (tab === 'partners') {
                        self.loadPartners();
                    }
                } else {
                    $(this).hide();
                }
            });
        }

        loadConversations() {
            const self = this;

            $.ajax({
                url: mmChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mm_chat_get_conversations',
                    nonce: mmChat.nonce,
                    limit: 20
                },
                success: function(response) {
                    if (response.success) {
                        self.conversations = response.data.conversations;
                        self.renderConversations();
                        self.updateUnreadBadge();
                    }
                }
            });
        }

        renderConversations() {
            const html = this.conversations.length === 0 
                ? '<div class="mm-chat-empty"><div class="mm-chat-empty-icon">💭</div><p class="mm-chat-empty-text">No conversations yet</p></div>'
                : this.conversations.map(conv => `
                    <div class="mm-conversation-item" data-id="${conv.id}">
                        <img src="${conv.other_user.avatar || 'https://via.placeholder.com/40'}" 
                             class="mm-conversation-avatar" alt="${conv.other_user.name}">
                        <div class="mm-conversation-info">
                            <p class="mm-conversation-name">${conv.other_user.name}</p>
                            <p class="mm-conversation-preview">${conv.last_message ? conv.last_message.text : 'No messages'}</p>
                        </div>
                        ${conv.unread_count > 0 ? `<span class="mm-conversation-unread">${conv.unread_count}</span>` : ''}
                    </div>
                `).join('');

            $('.mm-chat-conversations-list').html(html);
        }

        loadPartners() {
            const self = this;

            $.ajax({
                url: mmChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mm_chat_get_partners',
                    nonce: mmChat.nonce,
                    limit: 50
                },
                success: function(response) {
                    if (response.success) {
                        self.renderPartners(response.data.partners);
                    }
                }
            });
        }

        renderPartners(partners) {
            const html = partners.length === 0
                ? '<div class="mm-chat-empty"><div class="mm-chat-empty-icon">👥</div><p class="mm-chat-empty-text">No referral partners yet</p></div>'
                : partners.map(partner => `
                    <div class="mm-partner-item" data-id="${partner.id}" style="padding: 12px; border-bottom: 1px solid #dee2e6; cursor: pointer; display: flex; gap: 10px; align-items: center; transition: background 0.2s;">
                        <img src="${partner.avatar || 'https://via.placeholder.com/40'}" 
                             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div style="flex: 1;">
                            <p style="margin: 0; font-weight: 500; font-size: 14px;">${partner.name}</p>
                            <p style="margin: 4px 0 0 0; font-size: 12px; color: #6c757d;">@${partner.username}</p>
                        </div>
                    </div>
                `).join('');

            $('.mm-chat-partners-list').html(html);
        }

        filterPartners(searchTerm) {
            if (!searchTerm) {
                $('.mm-partner-item').show();
                return;
            }

            $('.mm-partner-item').each(function() {
                const name = $(this).find('p').first().text().toLowerCase();
                const username = $(this).find('p').eq(1).text().toLowerCase();
                
                if (name.includes(searchTerm) || username.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        openConversation(conversationId) {
            this.currentConversation = conversationId;
            
            // Show message window
            $('.mm-chat-conversations-list').parent().hide();
            $('.mm-chat-message-window').show();

            // Update header with other user info
            const conv = this.conversations.find(c => c.id === conversationId);
            if (conv) {
                $('.mm-chat-other-user-name').text(conv.other_user.name);
            }

            // Load messages
            this.loadMessages();
        }

        closeConversation() {
            this.currentConversation = null;
            
            // Hide message window
            $('.mm-chat-conversations-list').parent().show();
            $('.mm-chat-message-window').hide();
            
            // Reload conversations
            this.loadConversations();
        }

        loadMessages() {
            const self = this;

            if (!this.currentConversation) return;

            $.ajax({
                url: mmChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mm_chat_get_messages',
                    nonce: mmChat.nonce,
                    conversation_id: this.currentConversation,
                    limit: 50
                },
                success: function(response) {
                    if (response.success) {
                        self.renderMessages(response.data.messages);
                    }
                }
            });
        }

        renderMessages(messages) {
            const $messagesDiv = $('.mm-chat-message-window .mm-chat-messages');
            
            const html = messages.map(msg => {
                const isOwn = msg.sender_id === mmChat.currentUserId;
                const bubbleClass = isOwn ? 'sent' : 'received';
                const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                return `
                    <div class="mm-message-bubble ${bubbleClass}">
                        ${!isOwn ? `<img src="${msg.sender.avatar || 'https://via.placeholder.com/28'}" class="mm-message-avatar" alt="${msg.sender.name}">` : ''}
                        <div>
                            <div class="mm-message-text">${msg.message}</div>
                            <div class="mm-message-time">${time}</div>
                        </div>
                    </div>
                `;
            }).join('');

            $messagesDiv.html(html || '<div class="mm-chat-empty"><p>No messages yet</p></div>');
            
            // Scroll to bottom
            $messagesDiv.scrollTop($messagesDiv[0].scrollHeight);
        }

        sendMessage() {
            const $input = $('.mm-chat-input-field');
            const message = $input.val().trim();

            if (!message || !this.currentConversation) return;

            const self = this;

            $.ajax({
                url: mmChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mm_chat_send_message',
                    nonce: mmChat.nonce,
                    conversation_id: this.currentConversation,
                    message: message
                },
                success: function(response) {
                    if (response.success) {
                        $input.val('');
                        $input.css('height', 'auto');
                        self.loadMessages();
                    }
                }
            });
        }

        startConversation(userId) {
            const self = this;

            $.ajax({
                url: mmChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mm_chat_start_conversation',
                    nonce: mmChat.nonce,
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        self.switchTab('conversations');
                        self.loadConversations();
                        // Auto-open the new conversation
                        setTimeout(function() {
                            self.openConversation(response.data.conversation.id);
                        }, 500);
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Failed to start conversation');
                }
            });
        }

        updateUnreadBadge() {
            $.ajax({
                url: mmChat.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mm_chat_get_unread_count',
                    nonce: mmChat.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const count = response.data.unread_count;
                        const $badge = $('#mm-chat-badge');
                        
                        if (count > 0) {
                            $badge.text(count).removeClass('hidden');
                        } else {
                            $badge.addClass('hidden');
                        }
                    }
                }
            });
        }

        startPolling() {
            const self = this;
            
            this.pollingTimer = setInterval(function() {
                if (self.isOpen) {
                    if (self.currentConversation) {
                        self.loadMessages();
                    } else {
                        self.loadConversations();
                    }
                }
                self.updateUnreadBadge();
            }, mmChat.pollingInterval);
        }

        stopPolling() {
            if (this.pollingTimer) {
                clearInterval(this.pollingTimer);
                this.pollingTimer = null;
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        window.mmChatInstance = new MMChat();
    });

})(jQuery);
