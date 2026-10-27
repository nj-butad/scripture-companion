const form = document.querySelector('#chatForm');
const input = document.querySelector('#message');
const conversation = document.querySelector('#conversation');
const languageButtons = [...document.querySelectorAll('[data-language]')];
const aboutDialog = document.querySelector('#aboutDialog');
let language = 'en';

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
    input.focus();
  });
});

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
      body: JSON.stringify({ message, language, csrf: window.STILL_WATERS.csrf }),
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Request failed');
    status.remove();
    appendResponse(data);
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

