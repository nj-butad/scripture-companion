<?php

declare(strict_types=1);

$rosary = require __DIR__ . '/rosary.php';
$both = static fn(string $key): array => [
    'en' => $rosary['prayers']['en'][$key],
    'ceb' => $rosary['prayers']['ceb'][$key],
];

return [
    ['id' => 'sign-cross', 'category' => 'essential', 'when' => ['en' => 'To begin and end prayer', 'ceb' => 'Sa pagsugod ug pagtapos sa pag-ampo'], 'prayer' => $both('sign_cross'), 'keywords' => 'cross trinity krus amahan anak espiritu santo'],
    ['id' => 'our-father', 'category' => 'essential', 'when' => ['en' => 'The prayer Jesus taught us', 'ceb' => 'Ang pag-ampo nga gitudlo ni Hesus'], 'prayer' => $both('our_father'), 'keywords' => 'lord prayer amahan namo daily bread forgiveness'],
    ['id' => 'hail-mary', 'category' => 'marian', 'when' => ['en' => 'To ask Mary to pray with us', 'ceb' => 'Sa pagpangayo kang Maria nga mag-ampo uban kanato'], 'prayer' => $both('hail_mary'), 'keywords' => 'holy mary santa maria maghimaya mother god inahan dios'],
    ['id' => 'glory-be', 'category' => 'essential', 'when' => ['en' => 'A short prayer of praise', 'ceb' => 'Mubo nga pag-ampo sa pagdayeg'], 'prayer' => $both('glory_be'), 'keywords' => 'doxology praise trinity himaya'],
    ['id' => 'apostles-creed', 'category' => 'essential', 'when' => ['en' => 'To profess the faith we share', 'ceb' => 'Sa pagsugid sa atong gituohan'], 'prayer' => $both('creed'), 'keywords' => 'credo nagatuo belief faith'],
    [
        'id' => 'ten-commandments', 'category' => 'essential',
        'when' => ['en' => 'For daily Christian living and an examination of conscience', 'ceb' => 'Alang sa adlaw-adlaw nga Kristohanong kinabuhi ug pagsusi sa konsensya'],
        'prayer' => [
            'en' => [
                'title' => 'The Ten Commandments',
                'text' => "1. I am the Lord your God: you shall not have strange gods before me.\n2. You shall not take the name of the Lord your God in vain.\n3. Remember to keep holy the Lord’s Day.\n4. Honor your father and your mother.\n5. You shall not kill.\n6. You shall not commit adultery.\n7. You shall not steal.\n8. You shall not bear false witness against your neighbor.\n9. You shall not covet your neighbor’s wife.\n10. You shall not covet your neighbor’s goods.",
            ],
            'ceb' => [
                'title' => 'Ang Napulo ka mga Sugo',
                'text' => "1. Ako ang Ginoo nga imong Dios: dili ka magbaton ug laing mga dios gawas kanako.\n2. Dili nimo gamiton sa walay pulos ang ngalan sa Ginoo nga imong Dios.\n3. Hinumdomi nga balaanon ang Adlaw sa Ginoo.\n4. Tahura ang imong amahan ug inahan.\n5. Dili ka magpatay.\n6. Dili ka manapaw.\n7. Dili ka mangawat.\n8. Dili ka magsaksi ug bakak batok sa imong isigkatawo.\n9. Dili ka maibog sa asawa sa imong isigkatawo.\n10. Dili ka maibog sa kabtangan sa imong isigkatawo.",
            ],
        ],
        'keywords' => 'ten commandments decalogue ten sugo napulo kasugoan conscience konsensya christian living moral law exodus exodo',
    ],
    [
        'id' => 'st-michael', 'category' => 'protection',
        'when' => ['en' => 'For courage and protection from evil', 'ceb' => 'Alang sa kaisog ug panalipod batok sa dautan'],
        'prayer' => [
            'en' => ['title' => 'Prayer to St. Michael', 'text' => 'St. Michael the Archangel, defend us in battle. Be our protection against the wickedness and snares of the Devil. May God rebuke him, we humbly pray; and do thou, O Prince of the heavenly host, by the power of God, thrust into hell Satan and all the evil spirits who prowl about the world seeking the ruin of souls. Amen.'],
            'ceb' => ['title' => 'Pag-ampo kang San Miguel Arkanghel', 'text' => 'San Miguel Arkanghel, panalipdi kami sa pakig-away. Mahimo kang among panalipod batok sa kadaotan ug mga lit-ag sa yawa. Badlongon unta siya sa Dios, mapainubsanon kaming nangamuyo; ug ikaw, O Prinsipe sa langitnong kasundalohan, pinaagi sa gahum sa Dios, itambog ngadto sa impyerno si Satanas ug ang tanang dautang espiritu nga naglatagaw sa kalibotan sa pagpangita sa kalaglagan sa mga kalag. Amen.'],
        ], 'keywords' => 'archangel san miguel evil devil protection panalipod yawa',
    ],
    [
        'id' => 'guardian-angel', 'category' => 'protection',
        'when' => ['en' => 'For guidance and safekeeping', 'ceb' => 'Alang sa paggiya ug pagpanalipod'],
        'prayer' => [
            'en' => ['title' => 'Prayer to Your Guardian Angel', 'text' => 'Angel of God, my guardian dear, to whom God’s love commits me here, ever this day be at my side, to light and guard, to rule and guide. Amen.'],
            'ceb' => ['title' => 'Pag-ampo sa Anghel nga Magbalantay', 'text' => 'Anghel sa Dios, akong mahal nga magbalantay, nga gitugyan ako kanimo sa gugma sa Dios, kanunay ka unta sa akong kiliran niining adlawa, sa paglamdag ug pagbantay, sa pagmando ug paggiya. Amen.'],
        ], 'keywords' => 'angel guide child safety anghel magbalantay giya',
    ],
    [
        'id' => 'act-contrition', 'category' => 'essential',
        'when' => ['en' => 'When asking God’s forgiveness', 'ceb' => 'Sa pagpangayo sa kapasayloan sa Dios'],
        'prayer' => [
            'en' => ['title' => 'Act of Contrition', 'text' => 'O my God, I am heartily sorry for having offended you, and I detest all my sins because of your just punishments, but most of all because they offend you, my God, who are all good and deserving of all my love. I firmly resolve, with the help of your grace, to sin no more and to avoid the near occasions of sin. Amen.'],
            'ceb' => ['title' => 'Pagbasol', 'text' => 'O Dios ko, kinasingkasing akong nagbasol sa pagpasakit kanimo. Gidumtan ko ang tanan nakong mga sala tungod sa matarong mong silot, apan labaw sa tanan tungod kay nakapasakit kini kanimo, Dios ko, nga hingpit nga maayo ug takos sa tanan nakong gugma. Uban sa tabang sa imong grasya, lig-on akong nagtinguha nga dili na makasala ug likayan ang mga higayon sa sala. Amen.'],
        ], 'keywords' => 'confession sorry forgiveness repentance pagbasol sala pasaylo',
    ],
    [
        'id' => 'morning-offering', 'category' => 'daily',
        'when' => ['en' => 'At the beginning of the day', 'ceb' => 'Sa pagsugod sa adlaw'],
        'prayer' => [
            'en' => ['title' => 'Morning Offering', 'text' => 'O Jesus, through the Immaculate Heart of Mary, I offer you my prayers, works, joys, and sufferings of this day for all the intentions of your Sacred Heart, in union with the Holy Sacrifice of the Mass throughout the world, for the salvation of souls, the reparation of sins, the reunion of all Christians, and in particular for the intentions of the Holy Father this month. Amen.'],
            'ceb' => ['title' => 'Halad sa Kabuntagon', 'text' => 'O Hesus, pinaagi sa Putli nga Kasingkasing ni Maria, ihalad ko kanimo ang akong mga pag-ampo, mga buhat, mga kalipay ug mga kasakit niining adlawa alang sa tanang tinguha sa imong Balaang Kasingkasing, nahiusa sa Balaang Halad sa Misa sa tibuok kalibotan, alang sa kaluwasan sa mga kalag, sa pag-ayo sa mga sala, sa panaghiusa sa tanang Kristohanon, ug ilabi na alang sa mga tinguha sa Santo Papa karong bulana. Amen.'],
        ], 'keywords' => 'morning work school day kabuntagon adlaw eskwela',
    ],
    [
        'id' => 'come-holy-spirit', 'category' => 'holy-spirit',
        'when' => ['en' => 'Before study, work, or a hard decision', 'ceb' => 'Sa dili pa magtuon, magtrabaho, o mohimo og lisod nga desisyon'],
        'prayer' => [
            'en' => ['title' => 'Come, Holy Spirit', 'text' => 'Come, Holy Spirit, fill the hearts of your faithful and kindle in them the fire of your love. Send forth your Spirit and they shall be created, and you shall renew the face of the earth. Let us pray: O God, who by the light of the Holy Spirit instructed the hearts of the faithful, grant that by the same Holy Spirit we may be truly wise and ever rejoice in his consolations. Through Christ our Lord. Amen.'],
            'ceb' => ['title' => 'Umari Ka, Espiritu Santo', 'text' => 'Umari ka, Espiritu Santo, pun-a ang mga kasingkasing sa imong mga matinumanon ug dagkota diha kanila ang kalayo sa imong gugma. Ipadala ang imong Espiritu ug sila pagabuhaton, ug bag-ohon mo ang dagway sa yuta. Mag-ampo kita: O Dios, nga pinaagi sa kahayag sa Espiritu Santo nagtudlo sa mga kasingkasing sa mga matinumanon, itugot nga pinaagi sa mao nga Espiritu mahimong tinuod kaming maalamon ug kanunay magmaya sa iyang paghupay. Pinaagi ni Kristo among Ginoo. Amen.'],
        ], 'keywords' => 'holy spirit study exam wisdom decision espiritu santo tuon eksam kaalam',
    ],
    ['id' => 'hail-holy-queen', 'category' => 'marian', 'when' => ['en' => 'For hope and Mary’s intercession', 'ceb' => 'Alang sa paglaom ug pagpangamuyo ni Maria'], 'prayer' => $both('hail_holy_queen'), 'keywords' => 'salve regina queen mary rayna maria hope'],
    [
        'id' => 'angelus', 'category' => 'marian',
        'when' => ['en' => 'Traditionally at morning, noon, and evening', 'ceb' => 'Kasagarang iampo sa buntag, udto ug gabii'],
        'prayer' => [
            'en' => ['title' => 'The Angelus', 'text' => 'The Angel of the Lord declared unto Mary. And she conceived of the Holy Spirit. Hail Mary… Behold the handmaid of the Lord. Be it done unto me according to your word. Hail Mary… And the Word was made flesh. And dwelt among us. Hail Mary… Pray for us, O holy Mother of God, that we may be made worthy of the promises of Christ. Let us pray: Pour forth, we beseech you, O Lord, your grace into our hearts, that we, to whom the Incarnation of Christ your Son was made known by the message of an angel, may by his Passion and Cross be brought to the glory of his Resurrection. Through the same Christ our Lord. Amen.'],
            'ceb' => ['title' => 'Ang Angelus', 'text' => 'Ang Anghel sa Ginoo mipahibalo kang Maria. Ug nanamkon siya sa lalang sa Espiritu Santo. Maghimaya ka, Maria… Ania ang ulipon sa Ginoo. Mahimo unta dinhi kanako sumala sa imong pulong. Maghimaya ka, Maria… Ug ang Pulong nahimong Tawo. Ug mipuyo Siya uban kanato. Maghimaya ka, Maria… I-ampo mo kami, Mahal nga Inahan sa Dios. Aron mahimo kaming takos sa mga saad ni Jesucristo. Mag-ampo kita: Ang grasya mo, nangamuyo kami, Ginoo, ibubo sa among mga kalag, aron kami nga pinaagi sa pagpahibalo sa Anghel nasayod sa Pagkatawo ni Cristo imong Anak, tungod sa iyang kasakit ug kamatayon sa krus, madala ngadto sa mahimayaong pagkabanhaw. Kini among gipangayo pinaagi ni Cristo among Ginoo. Amen.'],
        ], 'keywords' => 'angelus incarnation noon bell angel anghel maria udto',
    ],
    [
        'id' => 'eternal-rest', 'category' => 'departed',
        'when' => ['en' => 'For family and friends who have died', 'ceb' => 'Alang sa pamilya ug mga higala nga namatay'],
        'prayer' => [
            'en' => ['title' => 'Eternal Rest', 'text' => 'Eternal rest grant unto them, O Lord, and let perpetual light shine upon them. May they rest in peace. Amen.'],
            'ceb' => ['title' => 'Dayon nga Kapahulayan', 'text' => 'Hatagi sila, O Ginoo, sa dayon nga kapahulayan, ug hayagi sila sa kahayag nga walay katapusan. Mangahulay unta sila sa kalinaw. Amen.'],
        ], 'keywords' => 'dead death funeral soul departed patay namatay kalag pahulay',
    ],
];
