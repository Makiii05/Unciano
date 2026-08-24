<?php
$fullName = trim(($student['last_name'] ?? '') . ', ' . ($student['first_name'] ?? '') . ' ' . ($student['middle_name'] ?? ''));
$typeLabel = match ($student['student_type'] ?? 'new') {
    'new' => 'New',
    'old' => 'Old',
    default => ucfirst($student['student_type'] ?? 'New'),
};
$statusValue = in_array($student['status'] ?? 'regular', ['regular','irregular']) ? $student['status'] : 'regular';
?>
<div class="mb-4">
    <a href="<?= url('views/department/enlistment/index.php' . ($termId ? '?term='.$termId : '')) ?>" class="inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Enlistment
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-slate-800"><?= e($fullName) ?></h3>
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-slate-50 rounded-xl px-4 py-3">
            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Student No.</p>
            <p class="text-sm font-mono font-medium text-slate-800"><?= e($student['student_number'] ?? '—') ?></p>
        </div>
        <div class="bg-slate-50 rounded-xl px-4 py-3">
            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Academic Term</p>
            <p class="text-sm font-medium text-slate-800"><?= e($term['description'] ?? '—') ?></p>
        </div>
        <div class="bg-slate-50 rounded-xl px-4 py-3">
            <p class="text-xs text-slate-400 uppercase tracking-wider mb-1">Student Type</p>
            <p id="student-type-label" class="text-sm font-medium text-slate-800"><?= e($typeLabel) ?></p>
        </div>
    </div>
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Year Level</label>
            <select id="student-level" class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-white">
                <option value="">Select year level</option>
                <?php foreach ($levels as $level): ?>
                    <option value="<?= (int) $level['id'] ?>" <?= (int)($student['level_id'] ?? 0) === (int) $level['id'] ? 'selected' : '' ?>><?= e($level['description']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
            <select id="student-status" class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-white">
                <option value="regular" <?= $statusValue==='regular'?'selected':'' ?>>Regular</option>
                <option value="irregular" <?= $statusValue==='irregular'?'selected':'' ?>>Irregular</option>
            </select>
        </div>
    </div>
    <p id="student-save-hint" class="text-xs text-slate-400 mt-2"></p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <p class="text-sm text-slate-500" id="enlistment-count"><?= count($enlistments) ?> subject(s) · <?= array_sum(array_map(fn($e)=> (int)($e['subjectOffering']['subject']['unit'] ?? 0), $enlistments)) ?> unit(s)</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="openAddSubjectModal()" class="inline-flex items-center gap-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Subject
            </button>
            <button onclick="openSectionsModal()" class="inline-flex items-center gap-1 px-4 py-2 bg-white border border-primary-300 text-primary-600 hover:bg-primary-50 text-sm font-medium rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Add by Section
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead><tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                <th class="px-6 py-4 text-left">Offering Code</th>
                <th class="px-6 py-4 text-left">Subject</th>
                <th class="px-6 py-4 text-left">Units</th>
                <th class="px-6 py-4 text-center">Final Grade</th>
                <th class="px-6 py-4 text-right">Actions</th>
            </tr></thead>
            <tbody id="enlistment-results-body">
                <?php if (empty($enlistments)): ?>
                    <tr><td colspan="5" class="py-12 text-center text-slate-400">No subjects enlisted for this student in the selected academic term.</td></tr>
                <?php else: foreach ($enlistments as $en): ?>
                    <tr class="border-t border-slate-100 hover:bg-slate-50" data-enlistment-id="<?= (int) $en['enlistment_id'] ?>" data-units="<?= (int)($en['subjectOffering']['subject']['unit'] ?? 0) ?>">
                        <td class="px-6 py-3.5 font-mono text-sm font-medium text-slate-800"><?= e($en['subjectOffering']['code'] ?? '') ?></td>
                        <td class="px-6 py-3.5 text-slate-600"><?= e($en['subjectOffering']['subject']['description'] ?? '') ?></td>
                        <td class="px-6 py-3.5 text-slate-600"><?= e($en['subjectOffering']['subject']['unit'] ?? 0) ?></td>
                        <td class="px-6 py-3.5 text-center font-medium text-slate-800"><?= $en['final_grade']!==null ? e(number_format((float)$en['final_grade'],2)) : '—' ?></td>
                        <td class="px-6 py-3.5 text-right"><button onclick="removeEnlistment(<?= (int)$en['enlistment_id'] ?>)" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded">Remove</button></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/partials/add-modal.php'; ?>
<?php include __DIR__ . '/partials/sections-modal.php'; ?>

<script>
    const studentId = <?= (int) $student['id'] ?>;
    const termId = <?= (int) $termId ?>;
    const sectionsUrl = "<?= url('api/department/enlistment/sections-by-term.php') ?>";
    const offeringsUrl = "<?= url('api/department/enlistment/offerings-by-term.php') ?>";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const enlistmentBody = document.getElementById('enlistment-results-body');
    const enlistmentCount = document.getElementById('enlistment-count');
    const typeLabel = document.getElementById('student-type-label');
    const levelSelect = document.getElementById('student-level');
    const statusSelect = document.getElementById('student-status');
    const saveHint = document.getElementById('student-save-hint');

    function refreshStats(){
        const rows = enlistmentBody.querySelectorAll('tr[data-enlistment-id]');
        const units = Array.from(rows).reduce((sum, row)=> sum + (parseInt(row.dataset.units,10)||0),0);
        enlistmentCount.textContent = `${rows.length} subject(s) · ${units} unit(s)`;
    }
    function applyStudentType(type){
        const label = type==='new'?'New': (type==='old'?'Old': (type||'—'));
        typeLabel.textContent = label.charAt(0).toUpperCase()+label.slice(1);
    }
    function enlistmentRow(enlistment){
        const offering = enlistment.subject_offering || enlistment.subjectOffering || {};
        const subject = offering.subject || {};
        const units = subject.unit || 0;
        const tr=document.createElement('tr');
        tr.className='border-t border-slate-100 hover:bg-slate-50';
        tr.dataset.enlistmentId=enlistment.id || enlistment.enlistment_id;
        tr.dataset.units=units;
        tr.innerHTML=`
            <td class="px-6 py-3.5 font-mono text-sm font-medium text-slate-800">${offering.code||''}</td>
            <td class="px-6 py-3.5 text-slate-600">${subject.description||''}</td>
            <td class="px-6 py-3.5 text-slate-600">${units}</td>
            <td class="px-6 py-3.5 text-center font-medium text-slate-800">${enlistment.final_grade!==null&&enlistment.final_grade!==undefined ? Number(enlistment.final_grade).toFixed(2) : '—'}</td>
            <td class="px-6 py-3.5 text-right"><button onclick="removeEnlistment(${enlistment.id || enlistment.enlistment_id})" class="px-3 py-1 text-xs font-medium text-red-500 hover:bg-red-50 rounded">Remove</button></td>
        `;
        return tr;
    }
    function showSectionsMessage(message,isError=false){
        const el=document.getElementById('sections-modal-message');
        el.textContent=message; el.classList.remove('hidden');
        el.className='text-sm rounded-xl px-4 py-3 '+(isError ? 'bg-red-50 border border-red-200 text-red-600' : 'bg-emerald-50 border border-emerald-200 text-emerald-600');
    }
    const sectionsModal=document.getElementById('sections-modal');
    const sectionsList=document.getElementById('sections-list');
    function openSectionsModal(){
        showSectionsMessage('',false); document.getElementById('sections-modal-message').classList.add('hidden');
        sectionsList.innerHTML='<p class="text-sm text-slate-400 py-4 text-center">Loading sections...</p>';
        sectionsModal.showModal();
        fetch(`${sectionsUrl}?academic_term_id=${termId}`)
            .then(r=>r.json()).then(sections=>{
                sectionsList.innerHTML='';
                if(!sections.length){ sectionsList.innerHTML='<p class="text-sm text-slate-400 py-4 text-center">No sections available for this academic term.</p>'; return; }
                sections.forEach(s=>{
                    const btn=document.createElement('button');
                    btn.type='button'; btn.className='w-full text-left px-4 py-3 rounded-xl border border-slate-200 hover:bg-primary-50 hover:border-primary-300 flex items-center justify-between';
                    btn.innerHTML=`<span class="text-sm font-medium text-slate-800">${s.section}</span><span class="px-2 py-0.5 bg-slate-100 rounded-full text-xs">${s.offerings_count} subject(s)</span>`;
                    btn.addEventListener('click',()=>addBySection(s.section));
                    sectionsList.appendChild(btn);
                });
            }).catch(()=>{ sectionsList.innerHTML='<p class="text-sm text-red-500 py-4 text-center">Failed to load sections.</p>'; });
    }
    function addBySection(section){
        const body=new FormData(); body.append('student_id',studentId); body.append('academic_term_id',termId); body.append('section',section);
        fetch("<?= url('api/department/enlistment/bulk-store.php') ?>",{method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body})
            .then(async r=>{const p=await r.json().catch(()=>({})); if(!r.ok) throw p; return p;})
            .then(payload=>{
                const emptyRow=enlistmentBody.querySelector('td[colspan]'); if(emptyRow) enlistmentBody.innerHTML='';
                payload.enlistments.forEach(e=> enlistmentBody.prepend(enlistmentRow(e)));
                refreshStats(); if(payload.student_type_changed) applyStudentType(payload.student_type);
                showSectionsMessage(payload.message);
            }).catch(err=>{ showSectionsMessage(err.message||'Something went wrong.', true);});
    }
    const addModal=document.getElementById('add-subject-modal');
    const addForm=document.getElementById('add-subject-form');
    const addErrorAlert=document.getElementById('add-error-alert');
    const offeringSelect=document.getElementById('add-subject_offering_id');
    function openAddSubjectModal(){
        addErrorAlert.classList.add('hidden');
        offeringSelect.disabled=true; offeringSelect.innerHTML='<option value="">Loading offerings...</option>';
        fetch(`${offeringsUrl}?academic_term_id=${termId}&student_id=${studentId}`)
            .then(r=>r.json()).then(offerings=>{
                if(!offerings.length){ offeringSelect.innerHTML='<option value="">No available offerings for this term</option>'; }
                else{
                    offeringSelect.innerHTML='<option value="">Select subject offering</option>';
                    offerings.forEach(o=>{
                        const opt=document.createElement('option'); opt.value=o.id;
                        const parts=[o.code, o.subject ? o.subject.description : ''];
                        if(o.program) parts.push(o.program.code);
                        if(o.level) parts.push(o.level.description);
                        opt.textContent=parts.filter(Boolean).join(' - ');
                        offeringSelect.appendChild(opt);
                    });
                }
                offeringSelect.disabled=false;
            }).catch(()=>{ offeringSelect.innerHTML='<option value="">Failed to load offerings</option>'; offeringSelect.disabled=false;});
        addModal.showModal();
    }
    addForm.addEventListener('submit', function(e){
        e.preventDefault(); addErrorAlert.classList.add('hidden');
        fetch(addForm.action,{method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:new FormData(addForm)})
            .then(async r=>{const p=await r.json().catch(()=>({})); if(!r.ok) throw p; return p;})
            .then(payload=>{
                const emptyRow=enlistmentBody.querySelector('td[colspan]'); if(emptyRow) enlistmentBody.innerHTML='';
                enlistmentBody.prepend(enlistmentRow(payload.enlistment));
                refreshStats(); if(payload.student_type_changed) applyStudentType(payload.student_type);
                addModal.close(); addForm.reset(); offeringSelect.disabled=true; offeringSelect.innerHTML='<option value="">Loading offerings...</option>';
            }).catch(err=>{
                addErrorAlert.textContent=(err.errors ? Object.values(err.errors).flat().join(' ') : (err.message||'Something went wrong.'));
                addErrorAlert.classList.remove('hidden');
            });
    });
    function removeEnlistment(id){
        fetch(`<?= url('api/department/enlistment/destroy.php') ?>?id=${id}`,{method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: new URLSearchParams({_token: csrfToken, id:id})})
            .then(async r=>{const p=await r.json().catch(()=>({})); if(!r.ok) throw p; return p;})
            .then(()=>{ const row=enlistmentBody.querySelector(`tr[data-enlistment-id="${id}"]`); if(row) row.remove(); refreshStats(); if(!enlistmentBody.children.length || enlistmentBody.querySelector('td[colspan]')){ /* keep empty */ }})
            .catch(()=>{});
    }
    function updateStudentDetails(){
        clearTimeout(updateStudentDetails.timer);
        saveHint.textContent='Saving...';
        updateStudentDetails.timer=setTimeout(()=>{
            fetch(`<?= url('api/department/enlistment/update-student.php') ?>?student_id=${studentId}`,{
                method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json','Content-Type':'application/json'},
                body: JSON.stringify({level_id: levelSelect.value, status: statusSelect.value, _token: csrfToken})
            }).then(async r=>{const p=await r.json().catch(()=>({})); if(!r.ok) throw p; return p;})
            .then(()=>{ saveHint.textContent='Saved.'; setTimeout(()=>{saveHint.textContent='';},2000);})
            .catch(()=>{ saveHint.textContent='Failed to save.';});
        },400);
    }
    levelSelect.addEventListener('change', updateStudentDetails);
    statusSelect.addEventListener('change', updateStudentDetails);
</script>
