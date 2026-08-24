<?php $to = $data['teacherOffering']; $off = $data['offering']; $period = $data['period']; $allowed = $data['periods']; $hasGrades = $data['hasGrades']; ?>
<div class="mb-4 flex items-center justify-between">
    <a href="<?= url('views/teacher/subject-load/index.php') ?>" class="inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:text-primary-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Subject Load
    </a>
    <span class="text-xs px-2 py-1 rounded-full <?= $hasGrades ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' ?>"><?= $hasGrades ? 'Grades already inputed' : 'No grades yet' ?></span>
</div>

<?php if ($data['gradeInputLocked']): ?>
<div class="mb-6 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-sm">Grade input is currently <strong>locked</strong> for this subject.</div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Section</p><p class="mt-1 font-medium text-slate-800"><?= e($off['offering_code'] ?? '—') ?></p></div>
        <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Academic Term</p><p class="mt-1 font-medium text-slate-800"><?= e($off['term_code'] ?? $off['academic_term_id'] ?? '—') ?></p></div>
        <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Subject</p><p class="mt-1 font-medium text-slate-800"><?= e($off['subject_code'] ?? '—') ?> - <?= e($off['subject_description'] ?? '') ?></p></div>
    </div>
    <div class="mt-6 pt-6 border-t border-slate-100">
        <div class="flex flex-wrap items-end gap-6">
            <div class="w-64">
                <label class="block text-sm font-medium text-slate-700 mb-1">Period</label>
                <form method="GET" action="<?= url('views/teacher/subject-load/grades.php') ?>">
                    <input type="hidden" name="loading_id" value="<?= (int)$to['id'] ?>">
                    <select name="period" class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-white" onchange="this.form.submit()">
                        <?php foreach ($allowed as $p): ?>
                            <option value="<?= e($p) ?>" <?= $period===$p?'selected':'' ?>><?= e(ucfirst($p)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="w-80">
                <label class="block text-sm font-medium text-slate-700 mb-1">Grading System</label>
                <div class="flex gap-2">
                    <select id="grading-system" class="flex-1 border border-slate-300 rounded-lg px-3 py-2 bg-white" onchange="updateGradingSystem(this.value)" <?= $data['gradeInputLocked'] ? 'disabled' : '' ?>>
                        <option value="">Use offering default</option>
                        <?php foreach ($data['gradingSystems'] as $gs): ?>
                            <option value="<?= (int)$gs['id'] ?>" <?= (int)($to['grading_id'] ?? 0) === (int)$gs['id'] ? 'selected' : '' ?>><?= e($gs['description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" onclick="openCreateGradingSystemModal()" class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm" <?= $data['gradeInputLocked'] ? 'disabled' : '' ?> title="Create">+</button>
                    <button type="button" onclick="editSelectedGradingSystem()" class="px-3 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-600 rounded-lg text-sm" <?= $data['gradeInputLocked'] ? 'disabled' : '' ?> title="Edit">&#9998;</button>
                    <button type="button" onclick="deleteSelectedGradingSystem()" class="px-3 py-2 bg-white border border-slate-300 hover:bg-red-50 text-red-500 rounded-lg text-sm" <?= $data['gradeInputLocked'] ? 'disabled' : '' ?> title="Delete">&#128465;</button>
                </div>
                <p id="grading-system-hint" class="text-xs mt-1 <?= $hasGrades ? 'text-amber-600' : 'text-slate-400' ?>"><?= $hasGrades ? 'Cannot change grading system: grades already inputed.' : 'Select grading system for this load.' ?></p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-700">Students (<?= count($data['students']) ?>) — Period: <?= e(ucfirst($period)) ?></h3>
        <span class="text-xs text-slate-400">Effective: <?= !empty($data['effectiveComponents']) ? e(implode(', ', array_column($data['effectiveComponents'],'code'))) : 'No grading system' ?></span>
    </div>
    <?php if (empty($data['students'])): ?>
        <div class="py-12 text-center text-slate-400 text-sm">No students are enrolled for this offering.</div>
    <?php elseif (empty($data['effectiveComponents'])): ?>
        <div class="p-6">
            <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-sm">This offering has no grading system assigned. Select a grading system above.</div>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead><tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                    <th class="px-4 py-3 text-left">Student</th>
                    <th class="px-4 py-3 text-left">No</th>
                    <?php foreach ($data['columns'] as $col): ?>
                        <th class="px-2 py-3 text-center"><?= e($col['component_code']) ?><?= e($col['column_number']) ?><br><span class="text-[10px]"><?= e($col['highest_score']) ?></span></th>
                    <?php endforeach; ?>
                    <th class="px-4 py-3 text-center">Grade</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($data['grades'] as $g): $name=trim(($g['last_name']??'').', '.($g['first_name']??'')); ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-2 text-sm font-medium text-slate-800"><?= e($name) ?></td>
                            <td class="px-4 py-2 font-mono text-sm text-slate-600"><?= e($g['student_number'] ?? '') ?></td>
                            <?php foreach ($data['columns'] as $col): ?>
                                <?php
                                // find raw score
                                $raw = null;
                                // Need to load raw_scores for this grade/column; simplified query inside loop? We'll query via db quickly? For now placeholder blank input
                                ?>
                                <td class="px-1 py-2 text-center"><input type="number" step="0.01" class="w-16 border border-slate-300 rounded px-1 py-1 text-center text-sm" placeholder="-" <?= $data['gradeInputLocked']?'disabled':'' ?>></td>
                            <?php endforeach; ?>
                            <td class="px-4 py-2 text-center text-sm font-semibold"><?= $g['initial_grade']!==null ? e($g['initial_grade']) : '-' ?></td>
                            <td class="px-4 py-2 text-center"><span class="px-2 py-0.5 rounded-full text-xs <?= $g['status']==='submitted'?'bg-blue-100 text-blue-800': ($g['status']==='approved'?'bg-green-100 text-green-800':'bg-slate-100 text-slate-600') ?>"><?= e($g['status'] ?? 'draft') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($data['grades'])): foreach ($data['students'] as $s): $name=trim(($s['last_name']??'').', '.($s['first_name']??'')); ?>
                        <tr class="border-t border-slate-100"><td class="px-4 py-2 text-sm font-medium text-slate-800"><?= e($name) ?></td><td class="px-4 py-2 font-mono text-sm text-slate-600"><?= e($s['student_number'] ?? '') ?></td><?php foreach ($data['columns'] as $col): ?><td class="px-1 py-2 text-center"><input type="number" class="w-16 border border-slate-300 rounded px-1 py-1 text-center text-sm" placeholder="-"></td><?php endforeach; ?><td class="px-4 py-2 text-center">-</td><td class="px-4 py-2 text-center"><span class="px-2 py-0.5 bg-slate-100 rounded-full text-xs">draft</span></td></tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Grading System Modals -->
<dialog id="grading-system-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6">
        <form id="grading-system-form">
            <input type="hidden" id="gs-method" value="POST">
            <input type="hidden" id="gs-id" value="">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-slate-800" id="gs-modal-title">New Grading System</h3>
                <button type="button" onclick="document.getElementById('grading-system-modal').close()" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Description</label><input type="text" id="gs-description" class="w-full border border-slate-300 rounded-lg px-3 py-2" placeholder="e.g. Custom Midterm" required></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Components</label><div id="gs-components-grid" class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-64 overflow-y-auto p-2 bg-slate-50 rounded-xl"></div></div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Total</label><input type="text" class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-slate-100" id="gs-total" value="0.00%" readonly><p id="gs-warning" class="text-xs text-red-500 mt-1 hidden">Exceeds 100%</p></div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="document.getElementById('grading-system-modal').close()" class="px-4 py-2 border border-slate-200 rounded-lg hover:bg-slate-50 text-sm">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm" id="gs-submit-btn">Create</button>
            </div>
        </form>
    </div>
</dialog>

<dialog id="delete-gs-modal" class="modal">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 text-center">
        <h3 class="text-base font-semibold text-slate-800 mb-2">Delete Grading System</h3>
        <p class="text-sm text-slate-600 mb-1">Delete <span id="delete-gs-target" class="font-medium"></span>?</p>
        <div class="flex justify-center gap-2 mt-4">
            <button onclick="document.getElementById('delete-gs-modal').close()" class="px-4 py-2 border border-slate-200 rounded-lg text-sm">Cancel</button>
            <button id="confirm-delete-gs-btn" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm">Delete</button>
        </div>
    </div>
</dialog>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const loadingId = <?= (int)$to['id'] ?>;
const hasGrades = <?= $hasGrades ? 'true' : 'false' ?>;
const updateGsUrl = "<?= url('api/teacher/grading-system/update.php') ?>";
const createGsUrl = "<?= url('api/teacher/grading-systems/store.php') ?>";
const updateGsConfigUrl = "<?= url('api/teacher/grading-systems/update.php') ?>";
const deleteGsUrl = "<?= url('api/teacher/grading-systems/delete.php') ?>";
const componentOptions = <?= json_encode($data['components'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
let allGradingSystems = <?= json_encode($data['gradingSystems'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;

function updateGradingSystem(gradingId){
    if(hasGrades){
        alert('Cannot change grading system: grades have already been inputed for this subject.');
        // revert select
        location.reload();
        return;
    }
    fetch(updateGsUrl, {method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify({teacher_offering_id: loadingId, grading_id: gradingId || null})})
        .then(async r=>{ const p=await r.json().catch(()=>({})); if(!r.ok) throw p; return p; })
        .then(()=> location.reload())
        .catch(err=>{ alert(err.message || 'Failed'); location.reload(); });
}

let gradingSystemModal = document.getElementById('grading-system-modal');
let gsComponentsGrid = document.getElementById('gs-components-grid');
let gsTotal = document.getElementById('gs-total');
let gsWarning = document.getElementById('gs-warning');

function renderGsComponents(checkedIds){
    gsComponentsGrid.innerHTML='';
    componentOptions.forEach(c=>{
        const label=document.createElement('label');
        label.className='flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg bg-white cursor-pointer hover:bg-slate-50';
        label.innerHTML=`<input type="checkbox" value="${c.id}" data-percentage="${c.percentage}" class="rounded" ${checkedIds.includes(c.id)?'checked':''}> <span class="text-sm">${c.code} - ${c.description}</span> <span class="text-xs text-slate-400 ml-auto">(${parseFloat(c.percentage).toFixed(2)}%)</span>`;
        gsComponentsGrid.appendChild(label);
    });
    gsComponentsGrid.querySelectorAll('input').forEach(cb=> cb.addEventListener('change', updateGsTotal));
    updateGsTotal();
}
function updateGsTotal(){
    let total=0; gsComponentsGrid.querySelectorAll('input:checked').forEach(cb=> total+=parseFloat(cb.dataset.percentage));
    gsTotal.value = total.toFixed(2)+'%';
    const exceeds = total>100;
    gsWarning.classList.toggle('hidden', !exceeds);
    document.getElementById('gs-submit-btn').disabled = exceeds;
}
function openCreateGradingSystemModal(){
    document.getElementById('gs-modal-title').textContent='New Grading System';
    document.getElementById('gs-submit-btn').textContent='Create';
    document.getElementById('gs-method').value='POST';
    document.getElementById('gs-id').value='';
    document.getElementById('gs-description').value='';
    renderGsComponents([]);
    gradingSystemModal.showModal();
}
function editSelectedGradingSystem(){
    const sel=document.getElementById('grading-system');
    const id=parseInt(sel.value);
    if(!id){ alert('Select grading system to edit'); return; }
    const gs=allGradingSystems.find(g=> parseInt(g.id)===id);
    if(!gs) return;
    if(hasGrades){ alert('Cannot edit: grades already inputed for this subject.'); return; }
    document.getElementById('gs-modal-title').textContent='Edit Grading System';
    document.getElementById('gs-submit-btn').textContent='Update';
    document.getElementById('gs-method').value='PUT';
    document.getElementById('gs-id').value=gs.id;
    document.getElementById('gs-description').value=gs.description;
    const checked=(gs.components||gs.component_ids||[]).map(c=> c.component_id||c.id||c);
    renderGsComponents(checked);
    gradingSystemModal.showModal();
}
let pendingDeleteGsId=null;
function deleteSelectedGradingSystem(){
    const sel=document.getElementById('grading-system');
    const id=parseInt(sel.value);
    if(!id){ alert('Select grading system to delete'); return; }
    if(hasGrades){ alert('Cannot delete: grades already inputed'); return; }
    const gs=allGradingSystems.find(g=> parseInt(g.id)===id);
    pendingDeleteGsId=id;
    document.getElementById('delete-gs-target').textContent='"'+(gs?gs.description:'')+'"';
    document.getElementById('delete-gs-modal').showModal();
}
document.getElementById('confirm-delete-gs-btn').addEventListener('click', ()=>{
    if(!pendingDeleteGsId) return;
    fetch(deleteGsUrl + '?id=' + pendingDeleteGsId, {method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: new URLSearchParams({_token: csrfToken, id: pendingDeleteGsId})})
        .then(async r=>{ const p=await r.json().catch(()=>({})); if(!r.ok) throw p; return p; })
        .then(()=> location.reload())
        .catch(err=> alert(err.message||'Failed'));
});

document.getElementById('grading-system-form').addEventListener('submit', function(e){
    e.preventDefault();
    const method=document.getElementById('gs-method').value;
    const id=document.getElementById('gs-id').value;
    const description=document.getElementById('gs-description').value.trim();
    const ids=[...gsComponentsGrid.querySelectorAll('input:checked')].map(cb=> parseInt(cb.value));
    if(!description){ alert('Description required'); return; }
    if(!ids.length){ alert('Select at least one component'); return; }
    const url = method==='PUT' ? updateGsConfigUrl + '?id=' + id : createGsUrl;
    const m = method==='PUT' ? 'POST' : 'POST';
    // For PUT we send POST with _method PUT equivalent via our endpoint which checks PUT-like
    fetch(url, {method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify({description, component_ids: ids, id})})
        .then(async r=>{ const p=await r.json().catch(()=>({})); if(!r.ok) throw p; return p; })
        .then(()=> location.reload())
        .catch(err=> alert(err.message||'Failed'));
});
</script>
