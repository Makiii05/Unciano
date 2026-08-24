<div class="mb-4">
    <a href="<?= url('views/department/teacher-loadings/index.php') ?>" class="inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Teacher Loadings
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h3 class="text-lg font-semibold text-slate-800"><?= e($fullName) ?></h3>
            <p class="text-sm text-slate-500 mt-0.5"><?= e($teacher['email'] ?? '') ?></p>
        </div>
        <span class="px-2 py-1 rounded-full text-xs <?= ($teacher['status']??'')==='active'?'bg-green-100 text-green-800':'bg-slate-100 text-slate-600' ?>"><?= e(ucfirst($teacher['status'] ?? 'unknown')) ?></span>
    </div>
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-slate-50 rounded-xl px-4 py-3"><p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Teacher Code</p><p class="text-sm font-mono font-medium text-slate-800"><?= e($teacher['code'] ?? '') ?></p></div>
        <div class="bg-slate-50 rounded-xl px-4 py-3"><p class="text-xs text-slate-400 uppercase tracking-wider mb-1">First Name</p><p class="text-sm font-medium text-slate-800"><?= e($teacher['first_name'] ?? '') ?></p></div>
        <div class="bg-slate-50 rounded-xl px-4 py-3"><p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Middle Name</p><p class="text-sm font-medium text-slate-800"><?= e($teacher['middle_name'] ?? '—') ?></p></div>
        <div class="bg-slate-50 rounded-xl px-4 py-3"><p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Last Name</p><p class="text-sm font-medium text-slate-800"><?= e($teacher['last_name'] ?? '') ?></p></div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-end gap-4 flex-wrap">
            <form method="GET" action="<?= url('views/department/teacher-loadings/show.php') ?>">
                <input type="hidden" name="teacher_id" value="<?= (int) $teacher['id'] ?>">
                <label class="block text-sm font-medium text-slate-700 mb-1">Academic Term</label>
                <select name="term" class="border border-slate-300 rounded-lg px-3 py-2 bg-white w-72" onchange="this.form.submit()">
                    <option value="">Select academic term</option>
                    <?php foreach ($terms as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= (int)$termId===(int)$t['id'] ? 'selected':'' ?>><?= e($t['description']) ?> <?= !empty($t['sy_description']) ? '(' . e($t['sy_description']) . ')' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <p class="text-sm text-slate-500 pb-2.5" id="loading-count"><?= count($loadings) ?> loading(s) for the selected term</p>
        </div>
        <button onclick="openAssignModal()" class="inline-flex items-center gap-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Assign Offering
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                <th class="px-6 py-4 text-left">Offering Code</th>
                <th class="px-6 py-4 text-left">Subject</th>
                <th class="px-6 py-4 text-left">Program</th>
                <th class="px-6 py-4 text-left">Year Level</th>
                <th class="px-6 py-4 text-left">Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr></thead>
            <tbody id="loading-results-body">
                <?php if (empty($loadings)): ?>
                    <tr><td colspan="6" class="py-12 text-center text-slate-400">No subject offerings assigned to this teacher for the selected term.</td></tr>
                <?php else: foreach ($loadings as $ld): ?>
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-6 py-3.5 font-mono text-sm font-medium text-slate-800"><?= e($ld['offering']['code'] ?? $ld['offering_code'] ?? '') ?></td>
                        <td class="px-6 py-3.5 text-slate-600"><?= e($ld['offering']['subject']['description'] ?? '') ?></td>
                        <td class="px-6 py-3.5 text-slate-600"><?= $ld['offering']['program'] ? e($ld['offering']['program']['code'] . ' - ' . $ld['offering']['program']['description']) : '—' ?></td>
                        <td class="px-6 py-3.5 text-slate-600"><?= e($ld['offering']['level']['description'] ?? '—') ?></td>
                        <td class="px-6 py-3.5"><span class="px-2 py-0.5 rounded-full text-xs <?= ($ld['status']??'active')==='active'?'bg-green-100 text-green-800':'bg-slate-100 text-slate-600' ?>"><?= e(ucfirst($ld['status'] ?? 'active')) ?></span></td>
                        <td class="px-6 py-3.5 text-right">
                            <div class="flex justify-end gap-1">
                                <a href="<?= url('views/department/teacher-loadings/class-list.php?loading_id=' . (int) $ld['loading_id']) ?>" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded">Class List</a>
                                <a href="<?= url('views/department/teacher-loadings/grade-sheet.php?loading_id=' . (int) $ld['loading_id']) ?>" class="px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 rounded border border-slate-200">Grade Sheet</a>
                                <button onclick="confirmDelete(<?= (int)$ld['loading_id'] ?>, '<?= e($ld['offering']['code'] ?? '') ?>')" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded">Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/partials/assign-modal.php'; ?>
<?php include __DIR__ . '/partials/delete-modal.php'; ?>

<script>
    const assignModal=document.getElementById('assign-modal');
    const assignForm=document.getElementById('assign-form');
    const assignErrorAlert=document.getElementById('assign-error-alert');
    const teacherSearch=document.getElementById('assign-teacher-search');
    const teacherResults=document.getElementById('assign-teacher-results');
    const teacherIdInput=document.getElementById('assign-teacher_id');
    const assignTermSelect=document.getElementById('assign-academic_term_id');
    const assignOfferingSelect=document.getElementById('assign-offering_id');
    const loadingBody=document.getElementById('loading-results-body');
    const loadingCount=document.getElementById('loading-count');
    const searchTeachersUrl="<?= url('api/department/teacher-loadings/search-teachers.php') ?>";
    const offeringsUrl="<?= url('api/department/teacher-loadings/offerings-by-term.php') ?>";
    const currentTeacherId=<?= (int)$teacher['id'] ?>;
    const currentTeacherName=<?= json_encode($fullName, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
    function openAssignModal(){
        teacherIdInput.value=currentTeacherId; teacherSearch.value=currentTeacherName;
        assignTermSelect.value='<?= $termId ?>';
        assignErrorAlert.classList.add('hidden'); assignOfferingSelect.disabled=true;
        if(assignTermSelect.value){ loadAssignOfferings(); } else { assignOfferingSelect.innerHTML='<option value="">Select academic term first</option>'; }
        assignModal.showModal();
    }
    let teacherDebounce;
    function loadTeachers(q){
        fetch(`${searchTeachersUrl}?q=${encodeURIComponent(q)}`).then(r=>r.json()).then(teachers=>{
            teacherResults.innerHTML='';
            if(!teachers.length){ teacherResults.innerHTML='<p class="px-4 py-3 text-sm text-slate-400">No teachers found.</p>'; }
            else{
                teachers.forEach(t=>{ const name=[t.last_name,t.first_name,t.middle_name].filter(Boolean).join(', '); const btn=document.createElement('button'); btn.type='button'; btn.className='w-full text-left px-4 py-2.5 hover:bg-primary-50 border-b border-slate-100 last:border-0'; btn.innerHTML=`<p class="text-sm font-medium text-slate-800">${t.code} - ${name}</p><p class="text-xs text-slate-400">${t.email}</p>`; btn.addEventListener('mousedown', e=>{ e.preventDefault(); selectTeacher(t,name); }); teacherResults.appendChild(btn); });
            }
            teacherResults.classList.remove('hidden');
        }).catch(()=>{});
    }
    function showTeacherResults(){ if(teacherResults.classList.contains('hidden')) loadTeachers(teacherSearch.value.trim()); }
    teacherSearch.addEventListener('input', ()=>{ clearTimeout(teacherDebounce); const q=teacherSearch.value.trim(); if(!q){ teacherResults.classList.add('hidden'); teacherResults.innerHTML=''; } teacherDebounce=setTimeout(()=>loadTeachers(q),300); });
    teacherSearch.addEventListener('focus', showTeacherResults);
    teacherSearch.addEventListener('click', showTeacherResults);
    teacherSearch.addEventListener('blur', ()=>{ setTimeout(()=>{ teacherResults.classList.add('hidden'); teacherResults.innerHTML=''; },150); });
    function selectTeacher(teacher,name){ teacherIdInput.value=teacher.id; teacherSearch.value=`${teacher.code} - ${name}`; teacherResults.classList.add('hidden'); teacherResults.innerHTML=''; loadAssignOfferings(); }
    assignTermSelect.addEventListener('change', loadAssignOfferings);
    function loadAssignOfferings(){
        const termId=assignTermSelect.value; assignOfferingSelect.disabled=true;
        if(!termId){ assignOfferingSelect.innerHTML='<option value="">Select academic term first</option>'; return; }
        assignOfferingSelect.innerHTML='<option value="">Loading offerings...</option>';
        fetch(`${offeringsUrl}?academic_term_id=${termId}&teacher_id=${teacherIdInput.value}`).then(r=>r.json()).then(offerings=>{
            if(!offerings.length){ assignOfferingSelect.innerHTML='<option value="">No subject offerings for this term</option>'; }
            else{ assignOfferingSelect.innerHTML='<option value="">Select subject offering</option>'; offerings.forEach(o=>{ const opt=document.createElement('option'); opt.value=o.id; const parts=[o.code, o.subject? o.subject.description:'']; if(o.program) parts.push(o.program.code); if(o.level) parts.push(o.level.description); opt.textContent=parts.filter(Boolean).join(' - '); assignOfferingSelect.appendChild(opt); }); }
            assignOfferingSelect.disabled=false;
        }).catch(()=>{ assignOfferingSelect.innerHTML='<option value="">Failed to load offerings</option>'; assignOfferingSelect.disabled=false; });
    }
    const csrfToken=document.querySelector('meta[name="csrf-token"]').content;
    assignForm.addEventListener('submit', function(e){
        e.preventDefault(); assignErrorAlert.classList.add('hidden');
        fetch(assignForm.action,{method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:new FormData(assignForm)})
            .then(async r=>{const p=await r.json().catch(()=>({})); if(!r.ok) throw p; return p;})
            .then(payload=>{
                const emptyRow=loadingBody.querySelector('td[colspan]'); if(emptyRow) loadingBody.innerHTML='';
                loadingBody.prepend(loadingRow(payload.loading));
                loadingCount.textContent=`${loadingBody.children.length} loading(s) for the selected term`;
                assignModal.close(); assignForm.reset(); teacherSearch.value=currentTeacherName; teacherIdInput.value=currentTeacherId;
            }).catch(err=>{ assignErrorAlert.textContent=(err.errors ? Object.values(err.errors).flat().join(' ') : (err.message||'Something went wrong.')); assignErrorAlert.classList.remove('hidden'); });
    });
    function loadingRow(loading){
        const tr=document.createElement('tr'); tr.className='border-t border-slate-100 hover:bg-slate-50';
        const offering=loading.offering||{}; const subject=offering.subject||{}; const program=offering.program||{}; const level=offering.level||{}; const programText=program.code? `${program.code} - ${program.description}`:'—'; const isActive=loading.status==='active';
        tr.innerHTML=`
            <td class="px-6 py-3.5 font-mono text-sm font-medium text-slate-800">${offering.code||''}</td>
            <td class="px-6 py-3.5 text-slate-600">${subject.description||''}</td>
            <td class="px-6 py-3.5 text-slate-600">${programText}</td>
            <td class="px-6 py-3.5 text-slate-600">${level.description||'—'}</td>
            <td class="px-6 py-3.5"><span class="px-2 py-0.5 rounded-full text-xs ${isActive?'bg-green-100 text-green-800':'bg-slate-100 text-slate-600'}">${isActive?'Active':'Inactive'}</span></td>
            <td class="px-6 py-3.5 text-right">
                <div class="flex justify-end gap-1">
                    <a href="<?= url('views/department/teacher-loadings/class-list.php') ?>?loading_id=${loading.id||loading.loading_id}" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded">Class List</a>
                    <a href="<?= url('views/department/teacher-loadings/grade-sheet.php') ?>?loading_id=${loading.id||loading.loading_id}" class="px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 rounded border border-slate-200">Grade Sheet</a>
                    <button onclick="confirmDelete(${loading.id||loading.loading_id}, '${offering.code||''}')" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded">Delete</button>
                </div>
            </td>`;
        return tr;
    }
    function confirmDelete(id,code){ document.getElementById('delete-form').action="<?= url('views/department/teacher-loadings/actions/delete.php') ?>"; document.getElementById('delete-target').textContent='"'+code+'"'; document.getElementById('delete-id').value=id; document.getElementById('delete-modal').showModal(); }
</script>
