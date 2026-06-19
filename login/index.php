<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SES FTU</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="../images/favicon.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Outfit', sans-serif;
        }
        @media (max-width: 640px) {
            body {
                padding: 16px !important;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-slate-100 to-[#1f4e78]/10 flex items-center justify-center min-h-screen p-4 sm:p-6 md:p-8">
    <div class="w-full max-w-md">
        
        <!-- Logo / Title centered on top -->
        <div class="text-center mb-8 select-none">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#1f4e78] text-[#ffc000] font-black text-2xl rounded-2xl shadow-md mb-3 border border-[#163754]">
                FTU
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Fallas de Tecnologías</h1>
            <p class="text-slate-500 text-sm mt-1 font-medium">Control de Seguimiento y Cierre de Incidentes</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-[#1f4e78]/15 border-t-4 border-t-[#1f4e78] p-6 md:p-8 flex flex-col justify-between w-full">
            <form method="POST" action="validator.php" class="flex flex-col justify-between space-y-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800 mb-5 flex items-center gap-2 select-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-4 h-4 text-[#1f4e78]">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <span class="text-[#1f4e78]">Acceso Seguro</span>
                    </h2>

                    <?php
                    session_start();
                    if (!empty($_SESSION["error-message"])):
                        $message = $_SESSION["error-message"];
                    ?>
                        <div class="mb-4 p-3 bg-red-50 border border-red-100 rounded-xl text-red-600 text-xs flex items-start gap-1.5 animate-pulse">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0 mt-0.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            <span><?= htmlspecialchars($message) ?></span>
                        </div>
                    <?php
                    endif;
                    session_destroy();
                    ?>

                    <div class="space-y-4">
                        <div>
                            <label for="user" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Usuario / Nómina</label>
                            <input type="text" id="user" name="user" required autocomplete="username"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1f4e78] focus:border-[#1f4e78] transition-colors text-xs"
                                   placeholder="Ingrese su nómina">
                        </div>

                        <div>
                            <label for="pass" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Contraseña</label>
                            <input type="password" id="pass" name="pass" required autocomplete="current-password"
                                   class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#1f4e78] focus:border-[#1f4e78] transition-colors text-xs"
                                   placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-2.5 bg-[#1f4e78] hover:bg-[#163754] text-white font-semibold rounded-xl transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1f4e78] text-xs flex items-center justify-center">
                        Entrar a la plataforma
                    </button>
                </div>
            </form>
        </div>

        <!-- Branding Footer -->
        <div class="text-center mt-8 text-xs text-slate-500 font-medium select-none space-y-2">
            <p>Sistema interno de SETTEPI Tijuana para el seguimiento preventivo y captura de incidentes tecnológicos en unidades.</p>
            <p class="text-slate-400 text-[10px]">&copy; <?= date('Y') ?> SETTEPI Tijuana. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
