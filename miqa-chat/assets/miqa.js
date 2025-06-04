(function () {
  // Append the floating button and chat box to body
  const btn = document.createElement('div');
  btn.className = 'miqa-float-btn';
  btn.innerHTML = '<i class="fa fa-comments fa-lg"></i>';
  document.body.appendChild(btn);

  const chatBox = document.createElement('div');
  chatBox.className = 'miqa-chat-box';
  chatBox.innerHTML = `
    <div class="miqa-chat-header">
      <span>MiQa Chat</span>
      <span style="cursor:pointer" id="miqaClose">&times;</span>
    </div>
    <div class="miqa-chat-body" id="miqaBody"></div>
    <form class="miqa-chat-input" id="miqaForm" autocomplete="off">
      <input type="text" id="miqaInput" placeholder="Type a message..." required />
      <button type="submit">Send</button>
    </form>
  `;
  document.body.appendChild(chatBox);

  // Event handlers
  btn.onclick = () => { chatBox.style.display = 'flex'; loadHistory(); }
  chatBox.querySelector('#miqaClose').onclick = () => { chatBox.style.display = 'none'; }
  chatBox.querySelector('#miqaForm').onsubmit = async function(e){
    e.preventDefault();
    const input = document.getElementById('miqaInput');
    const text = input.value.trim();
    if (!text) return;
    renderMsg('user', text);
    input.value = '';
    // Send to backend
    const resp = await fetch('/miqa-chat/api/messages.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({message: text})
    });
    const data = await resp.json();
    renderMsg('ai', data.reply);
    scrollDown();
  };

  // Render message
    function renderMsg(who, msg) {
    const body = document.getElementById('miqaBody');
    const el = document.createElement('div');
    el.className = 'miqa-chat-msg ' + who;

    // Use marked to parse Markdown, but only for 'ai' (bot) replies, or both if you want
    let safeHTML = (who === 'ai')
        ? marked.parse(msg)
        : escapeHTML(msg);

    el.innerHTML = '<b>' + (who === 'user' ? 'You' : 'MiQa') + ':</b><br>' + safeHTML;
    body.appendChild(el);
    scrollDown();
    }

  // Load history
  async function loadHistory() {
    const body = document.getElementById('miqaBody');
    body.innerHTML = '';
    const resp = await fetch('/miqa-chat/api/messages.php');
    const msgs = await resp.json();
    msgs.forEach(m => renderMsg(m.sender, m.message));
    scrollDown();
  }
  function scrollDown() {
    const body = document.getElementById('miqaBody');
    setTimeout(() => { body.scrollTop = body.scrollHeight; }, 100);
  }
  function escapeHTML(str) {
    return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }
})();
