<?php

require_once __DIR__ . '/../../config/bootstrap.php';


requereixLogin();

// L'id ve de la URL: s'ha de validar com a enter
if (!isset($_GET['id']) || !ctype_digit($_GET['id']) || (int) $_GET['id'] <= 0) {
    header('Location: leads.php');
    exit;
}

$id = (int) $_GET['id'];

$errorBD = null;
$lead = null;
$notas = [];
$errorNota = $_SESSION["errors_nota"] ?? null;
$textoAnterior = $_SESSION["old_nota"] ?? "";

//identificamos el usuario para obtener el rol y el id del usuario
$usuariNow = $usuariDAO->findByUsuari($_SESSION["usuari"]);

$usuariId = $usuariNow->getId();
$usuariRol = $usuariNow->getRol();

/*aqui ira unset */

try {
    $lead = $leadDAO->findById($id);
    if ($lead !== null) {
        $notas = $notaDAO->findByLead($id);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $errorBD = 'No s\'ha pogut accedir a la base de dades.';
}

if ($lead === null && $errorBD === null) {
    http_response_code(404);
}

$titol = 'TechLeads - Detall del lead';
require_once __DIR__ . '/../../vistes/dashboard_capcalera.php';
?>
<main class="max-w-3xl mx-auto px-6 py-8 flex-1 w-full space-y-6">

    <a href="leads.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-600 transition-colors">
        &larr; Tornar al llistat
    </a>

    <?php if (isset($_GET['desat'])): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            Els canvis s'han desat correctament.
        </div>
    <?php endif; ?>

    <?php if ($errorBD !== null): ?>
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            <?= e($errorBD) ?>
        </div>

    <?php elseif ($lead === null): ?>
        <div class="bg-white p-12 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-200/80 text-center space-y-2">
            <h1 class="text-xl font-extrabold text-slate-900">Lead no trobat</h1>
            <p class="text-sm text-slate-500">No existeix cap lead amb l'identificador indicat.</p>
        </div>

    <?php else: ?>
        <?php $color = colorEstat($lead->getEstat()); ?>
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-200/80 overflow-hidden">

            <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100">
                <div>
                    <div class="font-mono text-xs font-semibold text-slate-400">#<?= $lead->getId() ?></div>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight"><?= e($lead->getNom()) ?></h1>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-<?= $color ?>-50 text-<?= $color ?>-700 border border-<?= $color ?>-200">
                    <span class="h-1.5 w-1.5 rounded-full bg-<?= $color ?>-500"></span>
                    <?= e($lead->getEstat()) ?>
                </span>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 px-6 py-6 text-sm">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Correu electrònic</dt>
                    <dd class="mt-1">
                        <a href="mailto:<?= e($lead->getEmail()) ?>" class="text-indigo-600 hover:underline"><?= e($lead->getEmail()) ?></a>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Telèfon</dt>
                    <dd class="mt-1 text-slate-800"><?= e($lead->getTelefon() ?: '--') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Empresa</dt>
                    <dd class="mt-1 text-slate-800"><?= e($lead->getEmpresa() ?: '--') ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Pressupost</dt>
                    <dd class="mt-1 font-bold text-slate-800"><?= e(number_format($lead->getPressupost(), 2, ',', '.')) ?> €</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Serveis</dt>
                    <dd class="mt-1 flex flex-wrap gap-1">
                        <?php foreach ($lead->getServeis() as $servei): ?>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-medium"><?= e($servei) ?></span>
                        <?php endforeach; ?>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Missatge</dt>
                    <dd class="mt-1 text-slate-800"><?= nl2br(e($lead->getMissatge() ?: '--')) ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Document</dt>
                    <dd class="mt-1">
                        <?php if (!empty($lead->getDocument())): ?>
                            <a href="../pujades/<?= e($lead->getDocument()) ?>" class="text-indigo-600 hover:underline"><?= e($lead->getDocument()) ?></a>
                        <?php else: ?>
                            <span class="text-slate-800">--</span>
                        <?php endif; ?>
                    </dd>
                </div>
            </dl>

            <div class="flex justify-end px-6 py-4 border-t border-slate-100 bg-slate-50/60">
                <a href="editar.php?id=<?= $lead->getId() ?>"
                    class="inline-flex items-center px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-500/20 transition-all">
                    Editar
                </a>
            </div>
        </div>

        <!--NOTAS ULTIMA PRACTICA   -->
        <section id="notes" class="mt-12">
            <h2 class="text-2xl font-semibold text-slate-900 mb-6">
                ***Notes de seguiment
                <span class="ml-2 text-base font-normal text-slate-500"><?= e(count($notas)) ?></span>
            </h2>

            <!-- Formulari per afegir una nota -->
            <form action="afegir_nota.php" method="POST"
                class="bg-white border border-slate-200 rounded-xl p-6 mb-8 shadow-sm">
                <input type="hidden" name="lead_id" value="<?= e($lead->getId()) ?>">

                <label for="text" class="block text-base font-medium text-slate-700 mb-2">
                    Afig una nota
                </label>
                <textarea id="text" name="text" rows="4" maxlength="500" required
                    placeholder="Ex.: He telefonat al client, torne a cridar dijous."
                    class="w-full rounded-lg border border-slate-300 px-4 py-3 text-base leading-relaxed
                         focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"><?= e($textoAnterior) ?></textarea>

                <!-- Només mostrar si hi ha errors -->
                <?php if ($errorNota !== null): ?>

                    <p class="mt-2 text-sm text-red-600"><?= e($errorNota) ?></p>

                <?php endif ?>

                <div class="mt-4 flex items-center justify-between">
                    <span class="text-sm text-slate-500">Màxim 500 caràcters</span>
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-base font-medium
                           text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Afegir nota
                    </button>
                </div>
            </form>

            <!-- Historial de notes -->
            <!-- Mostrar si no hi ha notes -->
            <?php if (empty($notas)): ?>
                <p class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-slate-500">
                    Encara no hi ha notes per a aquest lead.
                </p>
            <?php endif ?>
            <!-- Mostrar si hi ha notes -->
            <ul class="space-y-4">
                <?php foreach ($notas as $nota): ?>
                    <li class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div class="text-sm text-slate-500">
                                <span class="font-medium text-slate-800"><?= e($nota->getNomUsuari()) ?></span>


                                <time datetime="{{data i hora en format dd/mm/yyyy}}">
                                    <?= e($nota->getDataCreacio()) ?>
                                </time>

                            </div>

                            <!-- Només mostrar el botó per eliminar si és administrador o el propietari de la nota-->
                             <?php if ($usuariRol === "administrador" || $nota->getUsuariId() === $usuariId):?>
                            <form action="eliminar_nota.php" method="POST"
                                onsubmit="return confirm('Segur que vols eliminar aquesta nota?');">

                                <input type="hidden" name="nota_id" value="<?= $nota->getId() ?>">
                                <input type="hidden" name="lead_id" value="<?= $lead->getId() ?>">

                                <button type="submit"
                                    class="text-sm text-red-600 hover:text-red-800 hover:underline">
                                    Eliminar
                                </button>
                            </form>
                                <?php endif ?>
                        </div>

                        <p class="mt-3 text-base leading-relaxed text-slate-800 whitespace-pre-line"><?= e($nota->getText()) ?></p>
                    </li>
                <?php endforeach ?>
            </ul>
        </section>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/../../vistes/dashboard_peu.php';
