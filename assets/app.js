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
const companionHome = document.querySelector('.companion-home');
const chatWidgetPanel = document.querySelector('#chatWidgetPanel');
const openChatWidget = document.querySelector('#openChatWidget');
const closeChatWidget = document.querySelector('#closeChatWidget');
const homeData = window.STILL_WATERS.home;
const lectioContent = document.querySelector('#lectioContent');
const lectioProgress = [...document.querySelectorAll('.lectio-progress span')];
const nextLectio = document.querySelector('#nextLectio');
const resetLectio = document.querySelector('#resetLectio');
const markMomentPrayed = document.querySelector('#markMomentPrayed');
const gratitudeText = document.querySelector('#gratitudeText');
const offerGratitude = document.querySelector('#offerGratitude');
let language = 'en';
let activeVerseIndex = Number(window.STILL_WATERS.todayIndex) || 0;
let lectioStage = 0;
const history = [];

function focusInitialConversation() {
  const arrivedAtPageTop = window.scrollY < 80;
  const hasSectionTarget = window.location.hash !== '';

  if (!arrivedAtPageTop || hasSectionTarget) return;

  companion.scrollIntoView({ behavior: 'auto', block: 'center' });
  input.focus({ preventScroll: true });
}

window.addEventListener('load', focusInitialConversation, { once: true });

function readStoredJson(key, fallback) {
  try {
    return JSON.parse(localStorage.getItem(key) || JSON.stringify(fallback));
  } catch (error) {
    return fallback;
  }
}

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
    renderHomeLanguage();
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

const lectioQuestions = {
  fear: { en: 'What fear can you place in God’s hands today?', ceb: 'Unsang kahadlok ang imong ikatugyan sa mga kamot sa Dios karon?' },
  grief: { en: 'Where do you most need God to remain close?', ceb: 'Asa nimo labing gikinahanglan nga magpabiling duol ang Dios?' },
  guidance: { en: 'Which decision needs trust more than control?', ceb: 'Unsang desisyon ang nagkinahanglan og pagsalig labaw sa pagkontrol?' },
  love: { en: 'Who is God inviting you to love patiently today?', ceb: 'Kinsa ang giagda sa Dios nga imong higugmaon uban sa pailob karon?' },
  peace: { en: 'What would it mean to receive Christ’s peace here?', ceb: 'Unsay kahulogan sa pagdawat sa kalinaw ni Cristo dinhi?' },
  default: { en: 'What word is God inviting you to carry into this day?', ceb: 'Unsang pulong ang giagda sa Dios nga imong dad-on niining adlawa?' },
};

nextLectio.addEventListener('click', () => {
  if (lectioStage < 2) {
    lectioStage += 1;
    renderLectio();
    return;
  }
  markPrayerDay();
  lectioStage = 3;
  renderLectio();
});

resetLectio.addEventListener('click', () => {
  lectioStage = 0;
  renderLectio();
});

function renderLectio() {
  const active = window.STILL_WATERS.weekVerses[activeVerseIndex].verses[language];
  const labels = language === 'ceb'
    ? [['1 · Pagbasa', 'Basaha paghinay ang bersikulo. Timan-i ang pulong o hugpong sa mga pulong nga nagpabilin kanimo.'], ['2 · Pamalandong', (lectioQuestions[active.topic] || lectioQuestions.default).ceb], ['3 · Pagtubag', 'Isulti sa Dios sa imong kaugalingong mga pulong unsa ang anaa sa imong kasingkasing.'], ['Nahuman', 'Pabilin sa kahilom sa makadiyot. Dad-a kining pulonga uban kanimo.']]
    : [['1 · Read', 'Read the passage slowly. Notice the word or phrase that stays with you.'], ['2 · Reflect', (lectioQuestions[active.topic] || lectioQuestions.default).en], ['3 · Respond', 'Tell God, in your own words, what is present in your heart.'], ['Complete', 'Rest in silence for a moment. Carry this word with you.']];
  const [step, instruction] = labels[lectioStage];
  const stepElement = document.createElement('p');
  stepElement.className = 'lectio-step';
  stepElement.textContent = step;
  const instructionElement = document.createElement('p');
  instructionElement.className = 'lectio-instruction';
  instructionElement.textContent = instruction;
  lectioContent.replaceChildren(stepElement, instructionElement);
  if (lectioStage === 0) {
    const quote = document.createElement('blockquote');
    quote.textContent = active.text;
    lectioContent.append(quote);
  }
  if (lectioStage === 2) {
    const prayer = document.createElement('textarea');
    prayer.rows = 3;
    prayer.maxLength = 800;
    prayer.placeholder = language === 'ceb' ? 'Isulat ang imong personal nga pag-ampo…' : 'Write your personal prayer…';
    prayer.setAttribute('aria-label', prayer.placeholder);
    lectioContent.append(prayer);
  }
  lectioProgress.forEach((dot, index) => dot.classList.toggle('is-active', index <= Math.min(lectioStage, 2)));
  resetLectio.hidden = lectioStage === 0;
  nextLectio.hidden = lectioStage === 3;
  nextLectio.firstChild.textContent = language === 'ceb'
    ? (lectioStage === 0 ? 'Sugdi ' : lectioStage === 2 ? 'Humanon ' : 'Sunod ')
    : (lectioStage === 0 ? 'Begin ' : lectioStage === 2 ? 'Complete ' : 'Next ');
}

document.querySelectorAll('[data-need]').forEach((button) => button.addEventListener('click', () => {
  const need = homeData.needs.find((item) => item.id === button.dataset.need);
  if (!need) return;
  if (!chatWidgetPanel.hidden) returnChatInline();
  input.value = need[language].prompt;
  companion.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', block: 'center' });
  window.setTimeout(() => input.focus(), reducedMotion() ? 0 : 320);
}));

document.querySelectorAll('[data-quiz-answer]').forEach((button) => button.addEventListener('click', () => {
  const choice = Number(button.dataset.quizAnswer);
  const correct = choice === Number(homeData.quiz.answer);
  const feedback = document.querySelector('#quizFeedback');
  document.querySelectorAll('[data-quiz-answer]').forEach((option, index) => {
    option.disabled = true;
    option.classList.toggle('is-correct', index === Number(homeData.quiz.answer));
    option.classList.toggle('is-wrong', option === button && !correct);
  });
  feedback.hidden = false;
  feedback.className = `quiz-feedback ${correct ? 'is-correct' : 'is-wrong'}`;
  feedback.textContent = `${correct ? (language === 'ceb' ? 'Sakto. ' : 'Correct. ') : (language === 'ceb' ? 'Dili pa. ' : 'Not quite. ')}${homeData.quiz.explanation[language]}`;
  localStorage.setItem('still-waters-quiz-date', window.STILL_WATERS.weekVerses[window.STILL_WATERS.todayIndex].date);
}));

markMomentPrayed.addEventListener('click', () => {
  markPrayerDay();
  markMomentPrayed.classList.add('is-complete');
  markMomentPrayed.innerHTML = `<span aria-hidden="true">✓</span> ${language === 'ceb' ? 'Giampo na' : 'Prayed today'}`;
});

offerGratitude.addEventListener('click', () => {
  if (!gratitudeText.value.trim()) {
    gratitudeText.focus();
    return;
  }
  markPrayerDay();
  gratitudeText.value = '';
  offerGratitude.textContent = language === 'ceb' ? 'Gipasalamatan na ✓' : 'Thanks offered ✓';
  window.setTimeout(() => {
    offerGratitude.textContent = language === 'ceb' ? 'Ihalad ang pasalamat' : 'Offer thanks';
  }, 1800);
});

form.addEventListener('click', (event) => {
  if (!event.target.closest('button')) input.focus();
});

function markPrayerDay() {
  const today = window.STILL_WATERS.weekVerses[window.STILL_WATERS.todayIndex].date;
  const rhythm = readStoredJson('still-waters-rhythm-v1', []);
  if (!rhythm.includes(today)) rhythm.push(today);
  localStorage.setItem('still-waters-rhythm-v1', JSON.stringify(rhythm.slice(-60)));
  renderRhythm();
}

function renderRhythm() {
  const rhythm = readStoredJson('still-waters-rhythm-v1', []);
  const week = document.querySelector('#rhythmWeek');
  const days = window.STILL_WATERS.weekVerses.map((day) => {
    const item = document.createElement('span');
    const prayed = rhythm.includes(day.date);
    item.className = `rhythm-day${prayed ? ' is-complete' : ''}`;
    item.innerHTML = `<i class="rhythm-dot" aria-hidden="true">${prayed ? '✓' : ''}</i><small>${language === 'ceb' ? day.day_ceb : day.day}</small>`;
    item.setAttribute('aria-label', `${day.day_long}: ${prayed ? 'prayed' : 'not marked'}`);
    return item;
  });
  week.replaceChildren(...days);
  const count = window.STILL_WATERS.weekVerses.filter((day) => rhythm.includes(day.date)).length;
  document.querySelector('#rhythmMessage').textContent = language === 'ceb'
    ? (count ? `${count} ka adlaw nga pag-ampo niining semanaha. Padayon lang sa kalumo.` : 'Usa ka malumong timaan sa imong pagbalik—dili kini grado.')
    : (count ? `${count} prayer ${count === 1 ? 'day' : 'days'} this week. Keep returning gently.` : 'A gentle record of showing up—never a score.');
  const today = window.STILL_WATERS.weekVerses[window.STILL_WATERS.todayIndex].date;
  if (rhythm.includes(today)) {
    markMomentPrayed.classList.add('is-complete');
    markMomentPrayed.innerHTML = `<span aria-hidden="true">✓</span> ${language === 'ceb' ? 'Giampo na' : 'Prayed today'}`;
  }
  const recent = readStoredJson('still-waters-recent-prayer', null);
  const recentLink = document.querySelector('#recentPrayerLink');
  if (recent?.title) recentLink.firstChild.textContent = `${language === 'ceb' ? 'Ablihi pag-usab' : 'Open again'}: ${recent.title} `;
}

function renderHomeLanguage() {
  const local = homeData.moment[language];
  document.querySelector('#momentLabel').textContent = local.label;
  document.querySelector('#momentTitle').textContent = local.title;
  document.querySelector('#momentText').textContent = local.text;
  homeData.needs.forEach((need) => {
    document.querySelector(`[data-need="${need.id}"] strong`).textContent = need[language].label;
  });
  document.querySelector('#quizQuestion').textContent = homeData.quiz.question[language];
  document.querySelectorAll('[data-quiz-answer]').forEach((button, index) => { button.textContent = homeData.quiz.options[index][language]; });
  const seasonal = homeData.seasonal[language];
  document.querySelector('#seasonalEyebrow').textContent = seasonal.eyebrow;
  document.querySelector('#seasonalTitle').textContent = seasonal.title;
  document.querySelector('#seasonalText').textContent = seasonal.text;
  document.querySelector('#seasonalAction').firstChild.textContent = `${seasonal.action} `;
  const term = homeData.term[language];
  document.querySelector('#termKicker').textContent = language === 'ceb' ? 'Katolikong pulong karon' : 'Catholic word for today';
  document.querySelector('#termName').textContent = term.term;
  document.querySelector('#termDefinition').textContent = term.definition;
  document.querySelector('#termPractice').innerHTML = `<strong>${language === 'ceb' ? 'Sulayi kini:' : 'Try this:'}</strong> ${term.practice}`;
  document.querySelector('#gratitudeTitle').textContent = language === 'ceb' ? 'Ngalani ang usa ka grasya' : 'Name one grace';
  document.querySelector('#gratitudeCopy').textContent = language === 'ceb' ? 'Unsang gamay nga gasa, kaayo, o tabang ang imong mapasalamatan sa Dios karon?' : 'What small gift, kindness, or moment of help can you thank God for today?';
  gratitudeText.placeholder = language === 'ceb' ? 'Karon, mapasalamaton ko sa…' : 'Today, I’m grateful for…';
  document.querySelector('#gratitudePrivacy').textContent = language === 'ceb' ? 'Magpabilin lamang dinhi' : 'Stays on this screen';
  offerGratitude.textContent = language === 'ceb' ? 'Ihalad ang pasalamat' : 'Offer thanks';
  renderLectio();
  renderRhythm();
}

async function loadTodayCelebration() {
  const today = window.STILL_WATERS.weekVerses[window.STILL_WATERS.todayIndex];
  try {
    const [year, month, day] = today.date.split('-');
    const response = await fetch(`https://cpbjr.github.io/catholic-readings-api/liturgical-calendar/${year}/${month}-${day}.json`);
    if (!response.ok) return;
    const data = await response.json();
    if (!data.celebration?.name) return;
    document.querySelector('#saintName').textContent = data.celebration.name;
    document.querySelector('#saintType').textContent = `${data.season || 'Liturgical calendar'} · ${(data.celebration.type || 'celebration').replaceAll('_', ' ')}`;
    document.querySelector('#saintDescription').textContent = data.celebration.description || (language === 'ceb'
      ? 'Bisan ang kasagarang adlaw balaan kung kini atong ihalad sa Dios.'
      : 'Even an ordinary weekday becomes holy when it is offered to God.');
    if (data.celebration.quote) document.querySelector('#saintPrompt').textContent = `“${data.celebration.quote}”`;
  } catch (error) {
    // The static calendar is an enhancement; the local fallback remains useful offline.
  }
}

renderLectio();
renderRhythm();
loadTodayCelebration();

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  const message = input.value.trim();
  if (!message) return;

  companion.classList.add('has-conversation');
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

