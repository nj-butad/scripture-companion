const form = document.querySelector('#chatForm');
const input = document.querySelector('#message');
const conversation = document.querySelector('#conversation');
const languageButtons = [...document.querySelectorAll('[data-language]')];
const aboutDialog = document.querySelector('#aboutDialog');
const dailyVerse = document.querySelector('#dailyVerse');
const dailyReference = document.querySelector('#dailyReference');
const dailyTranslation = document.querySelector('#dailyTranslation');
const dailyDate = document.querySelector('#dailyDate');
const dailySlide = document.querySelector('#dailySlide');
const weekDayButtons = [...document.querySelectorAll('[data-verse-day]')];
const previousVerse = document.querySelector('#previousVerse');
const nextVerse = document.querySelector('#nextVerse');
const versePosition = document.querySelector('#versePosition');
const companion = document.querySelector('#scriptureCompanion');
const companionHome = document.querySelector('#companionHome');
const chatWidgetPanel = document.querySelector('#chatWidgetPanel');
const openChatWidget = document.querySelector('#openChatWidget');
const closeChatWidget = document.querySelector('#closeChatWidget');
let language = 'en';
let activeVerseIndex = Number(window.STILL_WATERS.todayIndex) || 0;
const history = [];

const copy = {
  en: {
    welcome: 'What feels heavy today?',
    note: 'You can write naturally. Your message is not saved by this website.',
    placeholder: 'I feel anxious about what comes next…',
    sending: 'Finding a passage for this moment…',
    prayer: 'A prayer for this moment',
    next: 'A gentle next step',
    error: 'Something went wrong. Please try again in a moment.',
  },
  ceb: {
    welcome: 'Unsa ang bug-at sa imong kasingkasing karon?',
    note: 'Pagsulti lang sa natural nga paagi. Dili tipigan niini nga website ang imong mensahe.',
    placeholder: 'Nabalaka ko sa umaabot…',
    sending: 'Nangita og Kasulatan alang niining taknaa…',
    prayer: 'Usa ka pag-ampo alang niining taknaa',
    next: 'Usa ka malumong sunod nga lakang',
    error: 'Adunay problema. Palihog sulayi pag-usab sa makadiyot.',
  },
};

languageButtons.forEach((button) => {
  button.addEventListener('click', () => {
    language = button.dataset.language;
    languageButtons.forEach((item) => {
      const active = item === button;
      item.classList.toggle('is-active', active);
      item.setAttribute('aria-pressed', String(active));
    });
    document.documentElement.lang = language;
    document.querySelector('#welcomeText').textContent = copy[language].welcome;
    document.querySelector('#welcomeNote').textContent = copy[language].note;
    input.placeholder = copy[language].placeholder;
    renderDailyVerse(activeVerseIndex, false);
    input.focus();
  });
});

weekDayButtons.forEach((button) => button.addEventListener('click', () => {
  renderDailyVerse(Number(button.dataset.verseDay));
}));

previousVerse.addEventListener('click', () => renderDailyVerse((activeVerseIndex + 6) % 7));
nextVerse.addEventListener('click', () => renderDailyVerse((activeVerseIndex + 1) % 7));

let verseTouchStart = null;
dailySlide.addEventListener('touchstart', (event) => {
  verseTouchStart = event.changedTouches[0].clientX;
}, { passive: true });
dailySlide.addEventListener('touchend', (event) => {
  if (verseTouchStart === null) return;
  const distance = event.changedTouches[0].clientX - verseTouchStart;
  verseTouchStart = null;
  if (Math.abs(distance) < 45) return;
  renderDailyVerse(distance < 0 ? (activeVerseIndex + 1) % 7 : (activeVerseIndex + 6) % 7);
}, { passive: true });

function renderDailyVerse(index, animate = true) {
  const day = window.STILL_WATERS.weekVerses[index];
  if (!day) return;
  activeVerseIndex = index;
  const verse = day.verses[language];
  dailyDate.dateTime = day.date;
  dailyDate.textContent = day.date_label;
  dailyVerse.textContent = verse.text;
  dailyReference.textContent = verse.reference;
  dailyTranslation.textContent = verse.translation;
  document.querySelector('#dailyTitle').textContent = language === 'ceb'
    ? (day.is_today ? 'Bersikulo alang karong adlawa' : `Bersikulo alang sa ${day.day_long_ceb}`)
    : (day.is_today ? 'Verse for today' : `Verse for ${day.day_long}`);
  versePosition.textContent = `${index + 1} of 7`;
  weekDayButtons.forEach((button, buttonIndex) => {
    const active = buttonIndex === index;
    const buttonDay = window.STILL_WATERS.weekVerses[buttonIndex];
    button.querySelector('span').textContent = language === 'ceb' ? buttonDay.day_ceb : buttonDay.day;
    const todayLabel = button.querySelector('em');
    if (todayLabel) todayLabel.textContent = language === 'ceb' ? 'Karon' : 'Today';
    button.classList.toggle('is-active', active);
    button.setAttribute('aria-selected', String(active));
  });
  weekDayButtons[index].scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', inline: 'center', block: 'nearest' });
  if (animate && !reducedMotion()) {
    dailySlide.classList.remove('is-changing');
    requestAnimationFrame(() => dailySlide.classList.add('is-changing'));
  }
}

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  const message = input.value.trim();
  if (!message) return;

  appendUserMessage(message);
  input.value = '';
  input.disabled = true;
  const submitButton = form.querySelector('button[type="submit"]');
  submitButton.disabled = true;
  const status = appendStatus(copy[language].sending);

  try {
    const response = await fetch('api/chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        message,
        language,
        history: history.slice(-3),
        csrf: window.STILL_WATERS.csrf,
      }),
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Request failed');
    status.remove();
    appendResponse(data);
    if (data.type !== 'urgent') {
      history.push({
        user: message,
        assistant: [data.message, data.prayer, data.next_step].filter(Boolean).join(' '),
        passage_ids: (data.passages || []).map((passage) => passage.id).slice(0, 2),
      });
      if (history.length > 3) history.shift();
    }
  } catch (error) {
    status.textContent = error.message || copy[language].error;
    status.classList.add('is-error');
  } finally {
    input.disabled = false;
    submitButton.disabled = false;
    input.focus();
  }
});

input.addEventListener('keydown', (event) => {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();
    form.requestSubmit();
  }
});

document.querySelector('#aboutButton').addEventListener('click', () => aboutDialog.showModal());
document.querySelector('#closeAbout').addEventListener('click', () => aboutDialog.close());
aboutDialog.addEventListener('click', (event) => {
  if (event.target === aboutDialog) aboutDialog.close();
});

openChatWidget.addEventListener('click', () => {
  const opening = chatWidgetPanel.hidden;
  if (opening) {
    chatWidgetPanel.hidden = false;
    chatWidgetPanel.append(companion);
    document.body.classList.add('chat-widget-open');
    openChatWidget.setAttribute('aria-expanded', 'true');
    closeChatWidget.focus();
  } else {
    returnChatInline();
  }
});

closeChatWidget.addEventListener('click', returnChatInline);

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && !chatWidgetPanel.hidden) returnChatInline();
});

function returnChatInline() {
  companionHome.append(companion);
  chatWidgetPanel.hidden = true;
  document.body.classList.remove('chat-widget-open');
  openChatWidget.setAttribute('aria-expanded', 'false');
  openChatWidget.focus();
}

function appendUserMessage(message) {
  const article = document.createElement('article');
  article.className = 'user-message';
  const paragraph = document.createElement('p');
  paragraph.textContent = message;
  article.append(paragraph);
  conversation.append(article);
  article.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', block: 'end' });
}

function appendStatus(message) {
  const status = document.createElement('p');
  status.className = 'status-message';
  status.textContent = message;
  conversation.append(status);
  status.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', block: 'end' });
  return status;
}

function appendResponse(data) {
  const article = document.createElement('article');
  article.className = `guide-response${data.type === 'urgent' ? ' urgent-response' : ''}`;

  const reflection = document.createElement('p');
  reflection.className = 'reflection';
  reflection.textContent = data.message;
  article.append(reflection);

  (data.passages || []).forEach((passage) => {
    const figure = document.createElement('figure');
    figure.className = 'passage-card';
    const quote = document.createElement('blockquote');
    quote.textContent = passage.text;
    const caption = document.createElement('figcaption');
    const reference = document.createElement('strong');
    reference.textContent = passage.reference;
    const translation = document.createElement('span');
    translation.textContent = passage.translation;
    caption.append(reference, translation);
    figure.append(quote, caption);
    article.append(figure);
  });

  if (data.prayer) article.append(labelledSection(copy[language].prayer, data.prayer, 'prayer-block'));
  if (data.next_step) article.append(labelledSection(copy[language].next, data.next_step, 'next-step'));

  conversation.append(article);
  article.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', block: 'start' });
}

function labelledSection(label, text, className) {
  const section = document.createElement('section');
  section.className = className;
  const heading = document.createElement('h2');
  heading.textContent = label;
  const paragraph = document.createElement('p');
  paragraph.textContent = text;
  section.append(heading, paragraph);
  return section;
}

function reducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

