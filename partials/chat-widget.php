<?php

$chatWidgetInline = $chatWidgetInline ?? false;
?>
<aside class="chat-widget" id="chatWidget" aria-label="Private Scripture and prayer conversation">
    <div class="chat-widget-panel" id="chatWidgetPanel" hidden>
        <?php if (!$chatWidgetInline): ?>
            <iframe class="chat-widget-frame" title="Scripture and prayer conversation" data-src="/?chat=embed#share" loading="lazy"></iframe>
        <?php endif; ?>
    </div>
    <button class="chat-widget-launcher" id="openChatWidget" type="button" aria-label="What’s on your heart? Open a private Scripture and prayer conversation" aria-expanded="false" aria-controls="chatWidgetPanel">
        <span class="widget-bubble"><strong>What’s on your heart?</strong><span>Share how you feel. Find Scripture and prayer.</span></span>
        <span class="widget-mark" aria-hidden="true">
            <img class="kristohanon-widget-mark" src="/assets/images/kristohanon-holy-face-painted.webp" alt="" width="38" height="38">
        </span>
    </button>
</aside>
<?php if (!$chatWidgetInline): ?>
    <script src="/assets/global-widget.js?v=1" defer></script>
<?php endif; ?>
