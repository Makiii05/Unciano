<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-sm text-slate-500"><?= count($teachers) ?> teacher(s) with loadings</p>
        <button onclick="openAssignModal()" class="inline-flex items-center gap-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Assign Subject
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                <th class="px-6 py-4 text-left">Code</th>
                <th class="px-6 py-4 text-left">Name</th>
                <th class="px-6 py-4 text-left">Email</th>
                <th class="px-6 py-4 text-left">Loadings</th>
                <th class="px-6 py-4 text-left">Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr></thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr><td colspan="6" class="py-12 text-center text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        No teachers with loadings found. Assign a subject offering to a teacher to see them here.
                    </td></tr>
                <?php else: foreach ($teachers as $t): $fullName = trim(($t['last_name'] ?? '') . ', ' . ($t['first_name'] ?? '') . ' ' . ($t['middle_name'] ?? '')); ?>
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-6 py-3.5 font-mono text-sm text-slate-800"><?= e($t['code'] ?? $t['teacher_code'] ?? '') ?></td>
                        <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($fullName) ?></td>
                        <td class="px-6 py-3.5 text-slate-600"><?= e($t['email'] ?? '') ?></td>
                        <td class="px-6 py-3.5"><span class="px-2 py-0.5 bg-slate-100 rounded-full text-xs"><?= e($t['department_loadings_count'] ?? 0) ?> loading(s)</span></td>
                        <td class="px-6 py-3.5"><span class="px-2 py-0.5 rounded-full text-xs <?= ($t['status'] ?? '')==='active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' ?>"><?= e(ucfirst($t['status'] ?? 'unknown')) ?></span></td>
                        <td class="px-6 py-3.5 text-right"><a href="<?= url('views/department/teacher-loadings/show.php?teacher_id=' . (int) $t['id'] . ($termId ? '&term='.$termId : '')) ?>" class="px-3 py-1 text-xs font-medium text-primary-600 hover:bg-primary-50 rounded">View Subjects</a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/partials/assign-modal.php'; ?>

<script>
    const assignModal = document.getElementById('assign-modal');
    const assignForm = document.getElementById('assign-form');
    const assignErrorAlert = document.getElementById('assign-error-alert');
    const teacherSearch = document.getElementById('assign-teacher-search');
    const teacherResults = document.getElementById('assign-teacher-results');
    const teacherIdInput = document.getElementById('assign-teacher_id');
    const assignTermSelect = document.getElementById('assign-academic_term_id');
    const assignOfferingSelect = document.getElementById('assign-offering_id');
    const searchTeachersUrl = "<?= url('api/department/teacher-loadings/search-teachers.php') ?>";
    const offeringsUrl = "<?= url('api/department/teacher-loadings/offerings-by-term.php') ?>";

    function openAssignModal(){
        teacherIdInput.value=''; teacherSearch.value='';
        assignTermSelect.value='<?= $termId ?>';
        assignErrorAlert.classList.add('hidden');
        assignOfferingSelect.disabled=true;
        if(assignTermSelect.value){ loadAssignOfferings(); } else { assignOfferingSelect.innerHTML='<option value="">Select academic term first</option>'; }
        assignModal.showModal();
    }
    let teacherDebounce;
    function loadTeachers(q){
        fetch(`${searchTeachersUrl}?q=${encodeURIComponent(q)}`)
            .then(r=>r.json()).then(teachers=>{
                teacherResults.innerHTML='';
                if(!teachers.length){ teacherResults.innerHTML='<p class="px-4 py-3 text-sm text-slate-400">No teachers found.</p>'; }
                else{
                    teachers.forEach(t=>{
                        const name=[t.last_name,t.first_name,t.middle_name].filter(Boolean).join(', ');
                        const btn=document.createElement('button');
                        btn.type='button'; btn.className='w-full text-left px-4 py-2.5 hover:bg-primary-50 border-b border-slate-100 last:border-0';
                        btn.innerHTML=`<p class="text-sm font-medium text-slate-800">${t.code} - ${name}</p><p class="text-xs text-slate-400">${t.email}</p>`;
                        btn.addEventListener('mousedown', e=>{ e.preventDefault(); selectTeacher(t,name); });
                        teacherResults.appendChild(btn);
                    });
                }
                teacherResults.classList.remove('hidden');
            }).catch(()=>{});
    }
    function selectTeacher(teacher,name){ teacherIdInput.value=teacher.id; teacherSearch.value=`${teacher.code} - ${name}`; teacherResults.classList.add('hidden'); teacherResults.innerHTML=''; loadAssignOfferings(); }
    function showTeacherResults(){ if(teacherResults.classList.contains('hidden')) loadTeachers(teacherSearch.value.trim()); }
    teacherSearch.addEventListener('input', ()=>{ clearTimeout(teacherDebounce); const q=teacherSearch.value.trim(); if(!q){ teacherResults.classList.add('hidden'); teacherResults.innerHTML=''; } teacherDebounce=setTimeout(()=>loadTeachers(q),300); });
    teacherSearch.addEventListener('focus', showTeacherResults);
    teacherSearch.addEventListener('click', showTeacherResults);
    teacherSearch.addEventListener('blur', ()=>{ setTimeout(()=>{ teacherResults.classList.add('hidden'); teacherResults.innerHTML=''; },150); });
    assignTermSelect.addEventListener('change', loadAssignOfferings);
    function loadAssignOfferings(){
        const termId=assignTermSelect.value; assignOfferingSelect.disabled=true;
        if(!termId){ assignOfferingSelect.innerHTML='<option value="">Select academic term first</option>'; return; }
        assignOfferingSelect.innerHTML='<option value="">Loading offerings...</option>';
        fetch(`${offeringsUrl}?academic_term_id=${termId}&teacher_id=${teacherIdInput.value}`)
            .then(r=>r.json()).then(offerings=>{
                if(!offerings.length){ assignOfferingSelect.innerHTML='<option value="">No subject offerings for this term</option>'; }
                else{
                    assignOfferingSelect.innerHTML='<option value="">Select subject offering</option>';
                    offerings.forEach(o=>{ const opt=document.createElement('option'); opt.value=o.id; const parts=[o.code, o.subject? o.subject.description:'']; if(o.program) parts.push(o.program.code); if(o.level) parts.push(o.level.description); opt.textContent=parts.filter(Boolean).join(' - '); assignOfferingSelect.appendChild(opt); });
                }
                assignOfferingSelect.disabled=false;
            }).catch(()=>{ assignOfferingSelect.innerHTML='<option value="">Failed to load offerings</option>'; assignOfferingSelect.disabled=false; });
    }
    const csrfToken=document.querySelector('meta[name="csrf-token"]').content;
    assignForm.addEventListener('submit', function(e){
        e.preventDefault(); assignErrorAlert.classList.add('hidden');
        fetch(assignForm.action,{method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:new FormData(assignForm)})
            .then(async r=>{const p=await r.json().catch(()=>({})); if(!r.ok) throw p; return p;})
            .then(()=>{ assignModal.close(); window.location.reload(); })
            .catch(err=>{ assignErrorAlert.textContent=(err.errors ? Object.values(err.errors).flat().join(' ') : (err.message || 'Something went wrong.')); assignErrorAlert.classList.remove('hidden'); });
    });
</script>
