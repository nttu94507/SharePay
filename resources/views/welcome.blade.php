<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'SharePay') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */@layer theme{:root,:host{--font-sans:'Instrument Sans',ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";--font-serif:ui-serif,Georgia,Cambria,"Times New Roman",Times,serif;--font-mono:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;--color-slate-50:oklch(.984 .003 247.858);--color-slate-100:oklch(.968 .007 247.896);--color-slate-200:oklch(.929 .013 255.508);--color-slate-300:oklch(.869 .022 252.894);--color-slate-400:oklch(.704 .04 256.788);--color-slate-500:oklch(.554 .046 257.417);--color-slate-600:oklch(.446 .043 257.281);--color-slate-700:oklch(.372 .044 257.287);--color-slate-800:oklch(.279 .041 260.031);--color-slate-900:oklch(.208 .042 265.755);--color-white:#fff;--spacing:.25rem;--text-sm:.875rem;--text-base:1rem;--text-lg:1.125rem;--text-xl:1.25rem;--text-2xl:1.5rem;--text-3xl:1.875rem;--font-weight-medium:500;--font-weight-semibold:600;--font-weight-bold:700;--radius-lg:.5rem;--radius-xl:.75rem;--shadow-sm:0 1px 3px 0 #0000001a,0 1px 2px -1px #0000001a;--shadow-lg:0 10px 15px -3px #0000001a,0 4px 6px -4px #0000001a;--default-font-family:var(--font-sans)}
                @layer base{*,::before,::after{box-sizing:border-box;margin:0;padding:0}body{font-family:var(--default-font-family);background-color:var(--color-slate-50);color:var(--color-slate-900)}input,button,select,textarea{font:inherit}}
                @layer utilities{.min-h-screen{min-height:100vh}.mx-auto{margin-left:auto;margin-right:auto}.flex{display:flex}.flex-col{flex-direction:column}.gap-2{gap:.5rem}.gap-3{gap:.75rem}.gap-4{gap:1rem}.gap-6{gap:1.5rem}.gap-8{gap:2rem}.items-center{align-items:center}.justify-between{justify-content:space-between}.rounded-lg{border-radius:var(--radius-lg)}.rounded-xl{border-radius:var(--radius-xl)}.border{border:1px solid var(--color-slate-200)}.border-slate-200{border-color:var(--color-slate-200)}.bg-white{background-color:var(--color-white)}.bg-slate-100{background-color:var(--color-slate-100)}.p-3{padding:.75rem}.p-4{padding:1rem}.p-6{padding:1.5rem}.p-8{padding:2rem}.text-sm{font-size:var(--text-sm)}.text-base{font-size:var(--text-base)}.text-lg{font-size:var(--text-lg)}.text-xl{font-size:var(--text-xl)}.text-2xl{font-size:var(--text-2xl)}.text-3xl{font-size:var(--text-3xl)}.font-medium{font-weight:var(--font-weight-medium)}.font-semibold{font-weight:var(--font-weight-semibold)}.font-bold{font-weight:var(--font-weight-bold)}.shadow-sm{box-shadow:var(--shadow-sm)}.shadow-lg{box-shadow:var(--shadow-lg)}.w-full{width:100%}.max-w-4xl{max-width:64rem}.grid{display:grid}.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}.md\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}.md\:grid-cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}.transition{transition:.2s ease}.hover\:shadow-lg:hover{box-shadow:var(--shadow-lg)}.text-slate-500{color:var(--color-slate-500)}.text-slate-600{color:var(--color-slate-600)}.text-slate-700{color:var(--color-slate-700)}.text-slate-900{color:var(--color-slate-900)}.text-rose-600{color:#e11d48}.bg-emerald-600{background-color:#059669}.bg-emerald-700{background-color:#047857}.bg-slate-900{background-color:#0f172a}.text-white{color:#fff}.rounded-full{border-radius:9999px}.px-3{padding-left:.75rem;padding-right:.75rem}.py-2{padding-top:.5rem;padding-bottom:.5rem}.py-1{padding-top:.25rem;padding-bottom:.25rem}.text-center{text-align:center}.space-y-2> :not([hidden])~ :not([hidden]){margin-top:.5rem}.space-y-4> :not([hidden])~ :not([hidden]){margin-top:1rem}.hidden{display:none}.inline-flex{display:inline-flex}.flex-wrap{flex-wrap:wrap}.gap-1{gap:.25rem}.border-dashed{border-style:dashed}.bg-emerald-50{background-color:#ecfdf5}.bg-amber-50{background-color:#fffbeb}.text-amber-700{color:#b45309}.text-emerald-700{color:#047857}.h-10{height:2.5rem}.min-w-0{min-width:0}.truncate{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.w-24{width:6rem}.w-32{width:8rem}.w-40{width:10rem}.ring-1{box-shadow:0 0 0 1px rgba(15,23,42,.08)}.focus\:ring-2:focus{box-shadow:0 0 0 2px rgba(59,130,246,.5)}.focus\:outline-none:focus{outline:none}.cursor-pointer{cursor:pointer}.bg-slate-800{background-color:#1e293b}.hover\:bg-emerald-700:hover{background-color:#047857}.hover\:bg-slate-800:hover{background-color:#1e293b}}
            </style>
        @endif
    </head>
    <body>
        <div class="min-h-screen flex items-center">
            <div class="mx-auto w-full max-w-4xl p-6">
                <div class="bg-white rounded-xl shadow-lg p-8 space-y-8">
                    <header class="space-y-2">
                        <p class="text-sm text-slate-500">SharePay</p>
                        <h1 class="text-3xl font-bold">拆賬計算器</h1>
                        <p class="text-slate-600">先新增成員名字，再新增支出項目並選擇均分成員，系統會即時計算每個人需要負擔的金額。</p>
                    </header>

                    <section class="space-y-4" aria-labelledby="people-title">
                        <div class="flex items-center justify-between">
                            <h2 id="people-title" class="text-xl font-semibold">1. 新增成員名字</h2>
                            <p class="text-sm text-slate-500">目前成員：<span id="peopleCount">0</span> 人</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex flex-col gap-2">
                                <span class="text-sm font-medium">成員姓名</span>
                                <input id="personName" type="text" placeholder="例如：小美" class="border border-slate-200 rounded-lg p-3 focus:outline-none focus:ring-2" />
                            </label>
                            <div class="flex items-end">
                                <button id="addPerson" type="button" class="h-10 px-3 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition">新增成員</button>
                            </div>
                            <div class="flex items-end">
                                <p id="peopleNotice" class="text-sm text-rose-600 hidden">請輸入成員姓名。</p>
                            </div>
                        </div>
                        <div id="peopleList" class="flex flex-wrap gap-2"></div>
                    </section>

                    <section class="space-y-4" aria-labelledby="expense-title">
                        <div class="flex items-center justify-between">
                            <h2 id="expense-title" class="text-xl font-semibold">2. 新增支出項目</h2>
                            <p class="text-sm text-slate-500">若無法整除，餘額由第一位多付（依選取順序）。</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex flex-col gap-2">
                                <span class="text-sm font-medium">項目名稱</span>
                                <input id="expenseName" type="text" placeholder="例如：晚餐" class="border border-slate-200 rounded-lg p-3 focus:outline-none focus:ring-2" />
                            </label>
                            <label class="flex flex-col gap-2">
                                <span class="text-sm font-medium">金額</span>
                                <input id="expenseAmount" type="number" min="0" step="0.01" placeholder="例如：1200" class="border border-slate-200 rounded-lg p-3 focus:outline-none focus:ring-2" />
                            </label>
                            <div class="flex items-end">
                                <button id="addExpense" type="button" class="h-10 px-3 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition">新增項目</button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm font-medium">選擇均分成員</p>
                            <div id="participantList" class="grid grid-cols-1 md:grid-cols-3 gap-2 border border-dashed border-slate-200 rounded-lg p-4 text-sm text-slate-600">
                                <span>請先新增成員。</span>
                            </div>
                            <p id="expenseNotice" class="text-sm text-rose-600 hidden">請輸入完整資訊並至少選擇一位成員。</p>
                        </div>
                    </section>

                    <section class="space-y-4" aria-labelledby="summary-title">
                        <h2 id="summary-title" class="text-xl font-semibold">3. 分攤結果</h2>
                        <div id="expenseList" class="space-y-2 text-sm text-slate-600">
                            <p>尚未新增支出項目。</p>
                        </div>
                        <div id="totals" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                    </section>
                </div>
            </div>
        </div>

        <script>
            const personNameInput = document.getElementById('personName');
            const addPersonButton = document.getElementById('addPerson');
            const peopleNotice = document.getElementById('peopleNotice');
            const peopleList = document.getElementById('peopleList');
            const peopleCount = document.getElementById('peopleCount');
            const participantList = document.getElementById('participantList');
            const expenseName = document.getElementById('expenseName');
            const expenseAmount = document.getElementById('expenseAmount');
            const addExpenseButton = document.getElementById('addExpense');
            const expenseNotice = document.getElementById('expenseNotice');
            const expenseList = document.getElementById('expenseList');
            const totals = document.getElementById('totals');

            const state = {
                people: [],
                expenses: [],
            };

            const formatCurrency = (cents) => {
                return `NT$ ${(cents / 100).toFixed(2)}`;
            };

            const toCents = (value) => {
                const amount = Number.parseFloat(value);
                if (Number.isNaN(amount)) {
                    return null;
                }
                return Math.round(amount * 100);
            };

            const renderPeople = () => {
                peopleList.innerHTML = '';
                peopleCount.textContent = state.people.length;

                if (state.people.length === 0) {
                    peopleList.innerHTML = '<span class="text-sm text-slate-500">尚未新增成員。</span>';
                    participantList.innerHTML = '<span>請先新增成員。</span>';
                    return;
                }

                const fragment = document.createDocumentFragment();
                state.people.forEach((person) => {
                    const chip = document.createElement('span');
                    chip.className = 'inline-flex items-center gap-1 px-3 py-1 rounded-full bg-slate-100 text-sm text-slate-700';
                    chip.textContent = person.name;
                    fragment.appendChild(chip);
                });
                peopleList.appendChild(fragment);

                participantList.innerHTML = '';
                state.people.forEach((person, index) => {
                    const label = document.createElement('label');
                    label.className = 'flex items-center gap-2 cursor-pointer';
                    label.innerHTML = `
                        <input type="checkbox" class="participant-checkbox" value="${person.id}" ${index === 0 ? 'checked' : ''} />
                        <span>${person.name}</span>
                    `;
                    participantList.appendChild(label);
                });
            };

            const calculateTotals = () => {
                const totalsMap = new Map(state.people.map((person) => [person.id, 0]));

                state.expenses.forEach((expense) => {
                    if (expense.participants.length === 0) {
                        return;
                    }
                    const baseShare = Math.floor(expense.amountCents / expense.participants.length);
                    const remainder = expense.amountCents % expense.participants.length;

                    expense.participants.forEach((personId, index) => {
                        const extra = index === 0 ? remainder : 0;
                        totalsMap.set(personId, totalsMap.get(personId) + baseShare + extra);
                    });
                });

                return totalsMap;
            };

            const renderExpenses = () => {
                expenseList.innerHTML = '';

                if (state.expenses.length === 0) {
                    expenseList.innerHTML = '<p>尚未新增支出項目。</p>';
                } else {
                    const fragment = document.createDocumentFragment();
                    state.expenses.forEach((expense) => {
                        const item = document.createElement('div');
                        item.className = 'border border-slate-200 rounded-lg p-3 bg-slate-100';
                        const participantNames = expense.participants
                            .map((id) => state.people.find((person) => person.id === id)?.name)
                            .filter(Boolean)
                            .join('、');

                        item.innerHTML = `
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-slate-900">${expense.name}</span>
                                <span class="text-slate-700">${formatCurrency(expense.amountCents)}</span>
                            </div>
                            <p class="text-sm text-slate-600">均分成員：${participantNames || '未選擇'}</p>
                        `;
                        fragment.appendChild(item);
                    });
                    expenseList.appendChild(fragment);
                }

                totals.innerHTML = '';
                if (state.people.length === 0) {
                    return;
                }
                const totalsMap = calculateTotals();

                state.people.forEach((person) => {
                    const card = document.createElement('div');
                    const amount = totalsMap.get(person.id) ?? 0;
                    card.className = 'rounded-lg p-4 shadow-sm border border-slate-200 bg-emerald-50 text-emerald-700';
                    card.innerHTML = `
                        <p class="text-sm">${person.name}</p>
                        <p class="text-2xl font-semibold">${formatCurrency(amount)}</p>
                        <p class="text-sm">需負擔金額</p>
                    `;
                    totals.appendChild(card);
                });
            };

            const addPerson = () => {
                const name = personNameInput.value.trim();
                if (!name) {
                    peopleNotice.classList.remove('hidden');
                    return;
                }

                peopleNotice.classList.add('hidden');
                state.people.push({
                    id: `person-${state.people.length + 1}`,
                    name,
                });
                personNameInput.value = '';
                renderPeople();
                renderExpenses();
            };

            addPersonButton.addEventListener('click', addPerson);
            personNameInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    addPerson();
                }
            });

            addExpenseButton.addEventListener('click', () => {
                expenseNotice.classList.add('hidden');

                if (state.people.length === 0) {
                    expenseNotice.textContent = '請先新增成員。';
                    expenseNotice.classList.remove('hidden');
                    return;
                }

                const name = expenseName.value.trim() || '未命名項目';
                const amountCents = toCents(expenseAmount.value);
                const selectedParticipants = Array.from(document.querySelectorAll('.participant-checkbox:checked'))
                    .map((checkbox) => checkbox.value);

                if (!amountCents || selectedParticipants.length === 0) {
                    expenseNotice.textContent = '請輸入完整資訊並至少選擇一位成員。';
                    expenseNotice.classList.remove('hidden');
                    return;
                }

                state.expenses.push({
                    id: `expense-${state.expenses.length + 1}`,
                    name,
                    amountCents,
                    participants: selectedParticipants,
                });

                expenseName.value = '';
                expenseAmount.value = '';
                renderExpenses();
            });

            renderPeople();
            renderExpenses();
        </script>
    </body>
</html>
