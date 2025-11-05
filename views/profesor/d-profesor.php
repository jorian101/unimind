<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="Dashboard para la visualizacion de los datos de estres y ansiedad de los alumnos(solo por aulas)." name="descripcion"/>
    <title>Dashboard profesor</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    
    <script>
        tailwind.config = {
            darkMode: "class", 
            theme: {
                extend: {
                    colors: {
                        primary: "#33000A", 
                        "background-light": "#f6f6f8", 
                        "background-dark": "#111621"
                    }, 
                    fontFamily: {
                        display: "Lexend"
                    }, 
                    borderRadius: {
                        DEFAULT: "0.5rem", 
                        lg: "1rem", 
                        xl: "1.5rem", 
                        full: "9999px"
                    }
                }
            }
        };
    </script>
    
    <link rel="stylesheet" href="styles.css"> 
</head>
<body class="font-display bg-background-light dark:bg-background-dark">
    <div class="relative flex h-auto min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <div class="flex min-h-screen">
            <div class="flex flex-col gap-4 border-r border-gray-200 dark:border-gray-800 bg-white dark:bg-background-dark p-4 w-64">
                <div class="flex items-center gap-3 px-3 py-2">
                    <span class="material-symbols-outlined text-primary text-3xl">school</span>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">University</h1>
                </div>
                <div class="flex flex-col justify-between flex-1">
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary">
                            <span class="material-symbols-outlined text-primary">dashboard</span>
                            <p class="text-sm font-medium leading-normal text-primary">Dashboard</p>
                        </div>
                        
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                                <span class="material-symbols-outlined text-[#111318] dark:text-white">groups</span>
                                <p class="text-sm font-medium leading-normal text-[#111318] dark:text-white">My Classes</p>
                                <span class="material-symbols-outlined ml-auto text-gray-500 dark:text-gray-400">expand_less</span>
                            </div>
                            <div class="flex flex-col ml-6 pl-3 border-l border-gray-200 dark:border-gray-700">
                                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" href="#">
                                    <p class="text-sm font-medium leading-normal text-gray-900 dark:text-white">PSY101</p>
                                    </a>
                                <a class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" href="#">
                                    <p class="text-sm font-medium leading-normal text-gray-900 dark:text-white">SOC204</p>
                                </a>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <span class="material-symbols-outlined text-[#111318] dark:text-white">summarize</span>
                            <p class="text-sm font-medium leading-normal text-[#111318] dark:text-white">Reports</p>
                        </div>
                        <div class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <span class="material-symbols-outlined text-[#111318] dark:text-white">settings</span>
                            <p class="text-sm font-medium leading-normal text-[#111318] dark:text-white">Settings</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center gap-3 px-3 py-2">
                            <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10" data-alt="Portrait of Dr. Eleanor Vance" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBY_Bfdku0boJ4vqyJWlyuLD_VXKiVtKl3LWFTqhpQbDRVD2OQ2G-OiQjlhNo039YSPCcZ-SKUAkdzYqqIqi0N3mwLR0FGcHAiRe6AspO-691ZxOU2WC3PEP_aK9nrwxRr-_HhMfG7vZjrMRqdjkpEKGtL1mk31Yz5U28v7Yuk3zK9xImWyaWT-bCR-rPfqqnVEmFCuVvpXUWqfhyys2nyl_dOtcRKw6mw0aVDQDGq94MPd5y1KpR_tTFcBcnY9kKnD2EcKtYpXYUo");'></div>
                            <div class="flex flex-col">
                                <h1 class="text-[#111318] dark:text-white text-base font-medium leading-normal">Dr. Eleanor Vance</h1>
                                <p class="text-[#616f89] dark:text-gray-400 text-sm font-normal leading-normal">Psychology Dept.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <main class="flex-1 p-8">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
                    <div class="flex flex-col gap-1">
                        <p class="text-[#111318] dark:text-white text-3xl font-bold leading-tight tracking-tight">Teacher's Dashboard</p>
                        <p class="text-[#616f89] dark:text-gray-400 text-base font-normal leading-normal">Overview of student well-being across all classes.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button class="flex h-10 shrink-0 items-center justify-center gap-x-2 rounded-lg bg-white dark:bg-gray-800 pl-4 pr-3 border border-gray-200 dark:border-gray-700">
                            <p class="text-[#111318] dark:text-white text-sm font-medium leading-normal">Last 30 Days</p>
                            <span class="material-symbols-outlined text-[#111318] dark:text-white text-base">expand_more</span>
                        </button>
                        <button class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-[0.015em]">
                            <span class="truncate">Export Report</span>
                        </button>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <div class="flex flex-col gap-6 rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">PSY101 - Intro to Psychology</h3>
                        
                        <div class="flex flex-col gap-4">
                            <div class="flex items-start justify-between">
                                <div class="flex flex-col gap-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Stress Level</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">6.8 <span class="text-base font-medium text-gray-500 dark:text-gray-400">/ 10</span></p>
                                </div>
                                <div class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-900/50">
                                    <p class="text-sm font-medium text-amber-600 dark:text-amber-400">Moderate</p>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-amber-500 h-2.5 rounded-full" style="width: 68%"></div>
                            </div>
                            <div class="h-40">
                                <svg fill="none" height="100%" preserveaspectratio="none" viewbox="0 0 300 120" width="100%" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 81.5C30 81.5 30 96 60 96C90 96 90 60.5 120 60.5C150 60.5 150 42 180 42C210 42 210 57.5 240 57.5C270 57.5 270 73 300 73" stroke="#f59e0b" stroke-linecap="round" stroke-width="3"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-4">
                            <div class="flex items-start justify-between">
                                <div class="flex flex-col gap-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Anxiety Level</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">3.2 <span class="text-base font-medium text-gray-500 dark:text-gray-400">/ 10</span></p>
                                </div>
                                <div class="px-2.5 py-1 rounded-full bg-green-100 dark:bg-green-900/50">
                                    <p class="text-sm font-medium text-green-600 dark:text-green-400">Low</p>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-green-500 h-2.5 rounded-full" style="width: 32%"></div>
                            </div>
                            <div class="h-40">
                                <svg fill="none" height="100%" preserveaspectratio="none" viewbox="0 0 300 120" width="100%" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 43.5C30 43.5 30 25.5 60 25.5C90 25.5 90 59 120 59C150 59 150 81 180 81C210 81 210 63.5 240 63.5C270 63.5 270 41 300 41" stroke="#22c55e" stroke-linecap="round" stroke-width="3"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-6 rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">SOC204 - Social Problems</h3>
                        
                        <div class="flex flex-col gap-4">
                            <div class="flex items-start justify-between">
                                <div class="flex flex-col gap-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Stress Level</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">8.1 <span class="text-base font-medium text-gray-500 dark:text-gray-400">/ 10</span></p>
                                </div>
                                <div class="px-2.5 py-1 rounded-full bg-red-100 dark:bg-red-900/50">
                                    <p class="text-sm font-medium text-red-600 dark:text-red-400">High</p>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-red-500 h-2.5 rounded-full" style="width: 81%"></div>
                            </div>
                            <div class="h-40">
                                <svg fill="none" height="100%" preserveaspectratio="none" viewbox="0 0 300 120" width="100%" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 43C30 43 30 58.5 60 58.5C90 58.5 90 85 120 85C150 85 150 99.5 180 99.5C210 99.5 210 77 240 77C270 77 270 94 300 94" stroke="#ef4444" stroke-linecap="round" stroke-width="3"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-4">
                            <div class="flex items-start justify-between">
                                <div class="flex flex-col gap-1">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Anxiety Level</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">7.5 <span class="text-base font-medium text-gray-500 dark:text-gray-400">/ 10</span></p>
                                </div>
                                <div class="px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-900/50">
                                    <p class="text-sm font-medium text-amber-600 dark:text-amber-400">Moderate</p>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="bg-amber-500 h-2.5 rounded-full" style="width: 75%"></div>
                            </div>
                            <div class="h-40">
                                <svg fill="none" height="100%" preserveaspectratio="none" viewbox="0 0 300 120" width="100%" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 63C30 63 30 45.5 60 45.5C90 45.5 90 73 120 73C150 73 150 90 180 90C210 90 210 68 240 68C270 68 270 85.5 300 85.5" stroke="#f59e0b" stroke-linecap="round" stroke-width="3"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>