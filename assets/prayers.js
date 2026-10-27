(() => {
  const prayers = Array.isArray(window.PRAYERS_DATA) ? window.PRAYERS_DATA : [];
  const library = document.querySelector('#prayerLibrary');
  const search = document.querySelector('#prayerSearch');
  const count = document.querySelector('#prayerCount');
  const empty = document.querySelector('#emptyLibrary');
  const expandButton = document.querySelector('#expandPrayers');
  const categoriesElement = document.querySelector('#prayerCategories');
  const languageButtons = [...document.querySelectorAll('[data-prayer-language]')];
  const categories = {
    all: { en: 'All', ceb: 'Tanan' }, essential: { en: 'Essential', ceb: 'Panguna' },
    marian: { en: 'Marian', ceb: 'Alang kang Maria' }, protection: { en: 'Protection', ceb: 'Panalipod' },
    daily: { en: 'Daily', ceb: 'Adlaw-adlaw' }, 'holy-spirit': { en: 'Holy Spirit', ceb: 'Espiritu Santo' },
    departed: { en: 'For the departed', ceb: 'Alang sa namatay' }
  };
  let language = 'en';
  let category = 'all';

  const renderCategories = () => {
    const buttons = Object.keys(categories).map((key) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.dataset.category = key;
      button.className = `category-chip${key === category ? ' is-active' : ''}`;
      button.setAttribute('aria-pressed', key === category ? 'true' : 'false');
      button.textContent = categories[key][language];
      return button;
    });
    categoriesElement.replaceChildren(...buttons);
  };

  const createCard = (item) => {
    const details = document.createElement('details');
    details.className = 'library-card';
    const summary = document.createElement('summary');
    const heading = document.createElement('span');
    heading.className = 'library-card-heading';
    const title = document.createElement('strong');
    title.textContent = item.prayer[language].title;
    const cue = document.createElement('span');
    cue.textContent = item.when[language];
    heading.append(title, cue);
    const icon = document.createElement('span');
    icon.className = 'details-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = '+';
    summary.append(heading, icon);
    const body = document.createElement('div');
    body.className = 'library-card-body';
    const text = document.createElement('p');
    text.textContent = item.prayer[language].text;
    body.append(text);
    details.append(summary, body);
    details.addEventListener('toggle', () => {
      if (!details.open) return;
      localStorage.setItem('still-waters-recent-prayer', JSON.stringify({ id: item.id, title: item.prayer[language].title }));
    });
    return details;
  };

  const filteredPrayers = () => {
    const query = search.value.trim().toLocaleLowerCase();
    return prayers.filter((item) => {
      const categoryMatches = category === 'all' || item.category === category;
      const haystack = `${item.prayer.en.title} ${item.prayer.ceb.title} ${item.when.en} ${item.when.ceb} ${item.keywords}`.toLocaleLowerCase();
      return categoryMatches && (!query || haystack.includes(query));
    });
  };

  const render = () => {
    const matches = filteredPrayers();
    library.replaceChildren(...matches.map(createCard));
    count.textContent = language === 'en' ? `${matches.length} ${matches.length === 1 ? 'prayer' : 'prayers'}` : `${matches.length} ka pag-ampo`;
    empty.hidden = matches.length > 0;
    expandButton.hidden = matches.length === 0;
    expandButton.textContent = language === 'en' ? 'Open all' : 'Ablihi tanan';
    expandButton.dataset.open = 'false';
  };

  languageButtons.forEach((button) => button.addEventListener('click', () => {
    language = button.dataset.prayerLanguage;
    document.documentElement.lang = language === 'ceb' ? 'ceb' : 'en';
    languageButtons.forEach((option) => {
      const active = option === button;
      option.classList.toggle('is-active', active);
      option.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    search.placeholder = language === 'en' ? 'Search by prayer or need…' : 'Pangita og pag-ampo o panginahanglan…';
    renderCategories();
    render();
  }));
  categoriesElement.addEventListener('click', (event) => {
    const button = event.target.closest('[data-category]');
    if (!button) return;
    category = button.dataset.category;
    renderCategories();
    render();
  });
  search.addEventListener('input', render);
  expandButton.addEventListener('click', () => {
    const shouldOpen = expandButton.dataset.open !== 'true';
    library.querySelectorAll('details').forEach((details) => { details.open = shouldOpen; });
    expandButton.dataset.open = shouldOpen ? 'true' : 'false';
    expandButton.textContent = language === 'en' ? (shouldOpen ? 'Close all' : 'Open all') : (shouldOpen ? 'Sirad-i tanan' : 'Ablihi tanan');
  });
  renderCategories();
  render();
})();
