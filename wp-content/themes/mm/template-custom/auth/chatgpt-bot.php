<?php

/**
 * Template Name: ChatGPT Bot
 */

get_header();

// Only allow logged-in users
if (!is_user_logged_in()) {
    echo "<p>You must be logged in to access the Basic AI.</p>";
    get_footer();
    exit;
}
?>
<main>
    <div class="main-container" style="padding-top: 80px;">
        <div class="shadow mx-auto card">
            <div class="bg-primary text-white text-center card-header">
                <h5 class="mb-0">Basic AI</h5>
            </div>
            <div id="chat-box" class="overflow-auto card-body" style="height: 400px; background: #f8f9fa;"></div>
            <div class="p-3 card-footer">
                <div class="input-group">
                    <input type="text" id="user-input" class="form-control" placeholder="Type your message..." />
                    <button class="btn btn-primary" id="send-btn" type="button">Send</button>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    document.getElementById("send-btn").onclick = async () => {
        await sendMessage();
    };

    document.getElementById("user-input").addEventListener("keydown", async (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            await sendMessage();
        }
    });

    async function sendMessage() {
        const input = document.getElementById("user-input");
        const msg = input.value.trim();
        if (!msg) return;

        appendMessage("You", msg, "user");
        input.value = "";

        const res = await fetch("<?php echo admin_url('admin-ajax.php?action=chatgpt_ajax_handler'); ?>", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: msg
            })
        });

        const data = await res.json();

        if (data.reply) {
            appendMessage("AI", data.reply, "bot");
        } else {
            appendMessage("AI", "⚠️ AI response was empty or malformed.", "bot");
        }
    }

    function appendMessage(sender, text, cssClass) {
        const box = document.getElementById("chat-box");
        const div = document.createElement("div");
        div.className = `chat-msg ${cssClass} mb-2`;
        div.innerHTML = `<strong>${sender}:</strong> ${text}`;
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
    }
</script>

<?php get_footer(); ?>