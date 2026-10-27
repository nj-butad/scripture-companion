const data = window.ROSARY_DATA;
const languageButtons = [...document.querySelectorAll('[data-rosary-language]')];
const mysteryButtons = [...document.querySelectorAll('[data-mystery]')];
const ui = {
  stepKicker: document.querySelector('#stepKicker'),
  stepCount: document.querySelector('#stepCount'),
  progressBar: document.querySelector('#progressBar'),
  context: document.querySelector('#mysteryContext'),
  number: document.querySelector('#mysteryNumber'),
  name: document.querySelector('#mysteryName'),
  reference: document.querySelector('#mysteryReference'),
  beadLabel: document.querySelector('#beadLabel'),
  title: document.querySelector('#prayerTitle'),
  text: document.querySelector('#prayerText'),
  beads: document.querySelector('#decadeBeads'),
  previous: document.querySelector('#previousPrayer'),
  next: document.querySelector('#nextPrayer'),
  reset: document.querySelector('#resetRosary'),
  overviewTitle: document.querySelector('#overviewTitle'),
  mysteryList: document.querySelector('#mysteryList'),
  heroEyebrow: document.querySelector('#rosaryEyebrow'),
  heroTitle: document.querySelector('#rosaryTitle'),
  heroLede: document.querySelector('#rosaryLede'),
  overviewEyebrow: document.querySelector('#overviewEyebrow'),
  sourceNote: document.querySelector('#sourceNote'),
};

let language = 'en';
let mysteryKey = data.today;
let sequence = [];
let currentStep = 0;
let completed = false;

const labels = {
  en: {
    opening: 'Opening prayers', decade: (number) => `Decade ${number} of 5`, closing: 'Closing prayers',
    mystery: (number) => `${ordinal(number)} Mystery`, meditate: 'Pause and meditate on this mystery before continuing.',
    hailCount: (number) => `Hail Mary ${number} of 10`, step: (current, total) => `Step ${current} of ${total}`,
    previous: '← Previous', begin: 'Begin', next: 'Next prayer', finish: 'Complete Rosary', completed: 'Rosary complete',
    completedText: 'May this time of prayer draw you closer to Christ and remain with you in the day ahead.',
    startOver: 'Start over', today: 'Today', overview: (name) => name,
    mysteryShort: { joyful: 'Joyful', sorrowful: 'Sorrowful', glorious: 'Glorious', luminous: 'Luminous' },
    heroEyebrow: 'A guided prayer, one bead at a time', heroTitle: 'The Holy Rosary',
    heroLede: 'Today’s mysteries are chosen automatically for this day. You may select another set at any time.',
    overviewEyebrow: 'Today’s meditations',
    source: 'The mystery schedule and English prayer order follow the Holy See and USCCB. Cebuano prayer wording follows the Basilica Minore del Sto. Niño de Cebu.',
  },
  ceb: {
    opening: 'Pasiunang mga pag-ampo', decade: (number) => `Ika-${number} nga misteryo sa 5`, closing: 'Katapusang mga pag-ampo',
    mystery: (number) => `Ika-${number} nga Misteryo`, meditate: 'Paghilom ug pamalandongi kini nga misteryo sa dili pa mopadayon.',
    hailCount: (number) => `Maghimaya Ka Maria ${number} sa 10`, step: (current, total) => `Lakang ${current} sa ${total}`,
    previous: '← Balik', begin: 'Sugdi', next: 'Sunod nga pag-ampo', finish: 'Tapusa ang Rosaryo', completed: 'Nahuman ang Rosaryo',
    completedText: 'Hinaot nga kining panahon sa pag-ampo magpaduol kanimo kang Kristo ug magpabilin uban kanimo sa tibuok adlaw.',
    startOver: 'Sugdi pag-usab', today: 'Karon', overview: (name) => name,
    mysteryShort: { joyful: 'Kalipay', sorrowful: 'Kasakit', glorious: 'Himaya', luminous: 'Kahayag' },
    heroEyebrow: 'Usa ka giya sa pag-ampo, matag lubid', heroTitle: 'Ang Santos nga Rosaryo',
    heroLede: 'Ang mga misteryo alang karong adlawa awtomatikong gipili. Mahimo kang mopili og laing hugpong bisan kanus-a.',
    overviewEyebrow: 'Mga pamalandong karong adlawa',
    source: 'Ang han-ay sa mga misteryo ug Iningles nga mga pag-ampo nagsunod sa Holy See ug USCCB. Ang Cebuano nga mga pag-ampo gikan sa Basilica Minore del Sto. Niño de Cebu.',
  },
};

function ordinal(number) {
  return ['First', 'Second', 'Third', 'Fourth', 'Fifth'][number - 1];
}

function buildSequence() {
  const steps = [
    prayerStep('opening', 'sign_cross'),
    prayerStep('opening', 'creed'),
    prayerStep('opening', 'our_father'),
    ...[1, 2, 3].map((bead) => prayerStep('opening', 'hail_mary', null, bead)),
    prayerStep('opening', 'glory_be'),
  ];

  for (let decade = 1; decade <= 5; decade += 1) {
    steps.push({ section: 'decade', type: 'mystery', decade });
    steps.push(prayerStep('decade', 'our_father', decade));
    for (let bead = 1; bead <= 10; bead += 1) {
      steps.push(prayerStep('decade', 'hail_mary', decade, bead));
    }
    steps.push(prayerStep('decade', 'glory_be', decade));
    steps.push(prayerStep('decade', 'fatima', decade));
  }

  steps.push(prayerStep('closing', 'hail_holy_queen'));
  steps.push(prayerStep('closing', 'response'));
  steps.push(prayerStep('closing', 'closing'));
  steps.push(prayerStep('closing', 'sign_cross'));
  return steps;
}

function prayerStep(section, prayer, decade = null, bead = null) {
  return { section, type: 'prayer', prayer, decade, bead };
}

function render() {
  const copy = labels[language];
  const mystery = data.mysteries[mysteryKey][language];
  ui.heroEyebrow.textContent = copy.heroEyebrow;
  ui.heroTitle.textContent = copy.heroTitle;
  ui.heroLede.textContent = copy.heroLede;
  ui.overviewEyebrow.textContent = copy.overviewEyebrow;
  ui.sourceNote.textContent = copy.source;
  ui.overviewTitle.textContent = copy.overview(mystery.name);
  ui.mysteryList.replaceChildren(...mystery.items.map(([name, reference]) => {
    const item = document.createElement('li');
    const strong = document.createElement('strong');
    const span = document.createElement('span');
    strong.textContent = name;
    span.textContent = reference;
    item.append(strong, span);
    return item;
  }));

  languageButtons.forEach((button) => {
    const active = button.dataset.rosaryLanguage === language;
    button.classList.toggle('is-active', active);
    button.setAttribute('aria-pressed', String(active));
  });
  mysteryButtons.forEach((button) => {
    const active = button.dataset.mystery === mysteryKey;
    button.classList.toggle('is-active', active);
    button.setAttribute('aria-pressed', String(active));
    const chip = button.querySelector('.today-chip');
    button.firstChild.textContent = `${copy.mysteryShort[button.dataset.mystery]} `;
    if (chip) chip.textContent = copy.today;
  });
  document.documentElement.lang = language;
  ui.previous.textContent = copy.previous;
  ui.reset.textContent = copy.startOver;

  if (completed) {
    renderCompleted(copy);
    return;
  }

  const step = sequence[currentStep];
  ui.stepKicker.textContent = step.section === 'opening' ? copy.opening : step.section === 'closing' ? copy.closing : copy.decade(step.decade);
  ui.stepCount.textContent = copy.step(currentStep + 1, sequence.length);
  ui.progressBar.style.width = `${((currentStep + 1) / sequence.length) * 100}%`;
  ui.previous.disabled = currentStep === 0;

  const isLast = currentStep === sequence.length - 1;
  ui.next.querySelector('span').textContent = currentStep === 0 ? copy.begin : isLast ? copy.finish : copy.next;
  ui.next.disabled = false;

  if (step.decade) {
    const [name, reference] = mystery.items[step.decade - 1];
    ui.context.hidden = false;
    ui.number.textContent = String(step.decade);
    ui.name.textContent = name;
    ui.reference.textContent = reference;
    renderBeads(step);
  } else {
    ui.context.hidden = true;
    ui.beads.hidden = true;
  }

  if (step.type === 'mystery') {
    ui.beadLabel.textContent = copy.mystery(step.decade);
    ui.title.textContent = mystery.items[step.decade - 1][0];
    ui.text.textContent = copy.meditate;
  } else {
    const prayer = data.prayers[language][step.prayer];
    ui.beadLabel.textContent = step.prayer === 'hail_mary' && step.decade ? copy.hailCount(step.bead) : '';
    ui.title.textContent = prayer.title;
    ui.text.textContent = prayer.text;
  }

  saveProgress();
}

function renderBeads(step) {
  ui.beads.hidden = false;
  const completedBeads = step.prayer === 'hail_mary' ? Math.max(0, step.bead - 1) : ['glory_be', 'fatima'].includes(step.prayer) ? 10 : 0;
  const activeBead = step.prayer === 'hail_mary' ? step.bead : 0;
  const beads = [];
  for (let bead = 1; bead <= 10; bead += 1) {
    const dot = document.createElement('span');
    dot.className = `bead${bead <= completedBeads ? ' is-complete' : ''}${bead === activeBead ? ' is-current' : ''}`;
    dot.setAttribute('aria-label', `Bead ${bead}${bead === activeBead ? ', current' : bead <= completedBeads ? ', complete' : ''}`);
    beads.push(dot);
  }
  ui.beads.replaceChildren(...beads);
}

function renderCompleted(copy) {
  ui.stepKicker.textContent = copy.completed;
  ui.stepCount.textContent = copy.step(sequence.length, sequence.length);
  ui.progressBar.style.width = '100%';
  ui.context.hidden = true;
  ui.beads.hidden = true;
  ui.beadLabel.textContent = '✦';
  ui.title.textContent = copy.completed;
  ui.text.textContent = copy.completedText;
  ui.previous.disabled = false;
  ui.next.querySelector('span').textContent = '✓';
  ui.next.disabled = true;
  saveProgress();
}

function saveProgress() {
  localStorage.setItem(storageKey(), JSON.stringify({ currentStep, completed }));
}

function loadProgress() {
  try {
    const saved = JSON.parse(localStorage.getItem(storageKey()) || '{}');
    currentStep = Number.isInteger(saved.currentStep) ? Math.min(Math.max(saved.currentStep, 0), sequence.length - 1) : 0;
    completed = saved.completed === true;
  } catch {
    currentStep = 0;
    completed = false;
  }
}

function storageKey() {
  return `still-waters-rosary-${data.date}-${mysteryKey}`;
}

languageButtons.forEach((button) => button.addEventListener('click', () => {
  language = button.dataset.rosaryLanguage;
  render();
}));

mysteryButtons.forEach((button) => button.addEventListener('click', () => {
  mysteryKey = button.dataset.mystery;
  currentStep = 0;
  completed = false;
  render();
}));

ui.previous.addEventListener('click', () => {
  if (completed) completed = false;
  else currentStep = Math.max(0, currentStep - 1);
  render();
});

ui.next.addEventListener('click', () => {
  if (currentStep === sequence.length - 1) completed = true;
  else currentStep += 1;
  render();
});

ui.reset.addEventListener('click', () => {
  currentStep = 0;
  completed = false;
  render();
});

sequence = buildSequence();
loadProgress();
render();
