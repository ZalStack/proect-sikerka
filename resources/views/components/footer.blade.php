<footer class="bg-white border-t border-gray-200">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Tentang Kami</h3>
                <p class="text-gray-600 text-sm">
                    Sistem E-Learning Karyawan berbasis web untuk meningkatkan kompetensi dan pengetahuan karyawan.
                </p>
                <div class="mt-4 flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-blue-600 transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-blue-600 transition">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-blue-600 transition">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-blue-600 transition">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Link Cepat</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-600 text-sm hover:text-blue-600 transition">Dashboard</a></li>
                    <li><a href="#" class="text-gray-600 text-sm hover:text-blue-600 transition">Materi</a></li>
                    <li><a href="#" class="text-gray-600 text-sm hover:text-blue-600 transition">Quiz</a></li>
                    <li><a href="#" class="text-gray-600 text-sm hover:text-blue-600 transition">Bantuan</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Dukungan</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-600 text-sm hover:text-blue-600 transition">FAQ</a></li>
                    <li><a href="#" class="text-gray-600 text-sm hover:text-blue-600 transition">Kontak</a></li>
                    <li><a href="#" class="text-gray-600 text-sm hover:text-blue-600 transition">Kebijakan Privasi</a></li>
                    <li><a href="#" class="text-gray-600 text-sm hover:text-blue-600 transition">Syarat & Ketentuan</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Kontak</h3>
                <ul class="space-y-3">
                    <li class="flex items-start space-x-3 text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt text-blue-600 mt-1"></i>
                        <span>Jl. Pendidikan No. 123, Jakarta</span>
                    </li>
                    <li class="flex items-center space-x-3 text-sm text-gray-600">
                        <i class="fas fa-phone text-blue-600"></i>
                        <span>+62 812 3456 7890</span>
                    </li>
                    <li class="flex items-center space-x-3 text-sm text-gray-600">
                        <i class="fas fa-envelope text-blue-600"></i>
                        <span>info@elearning.com</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="mt-8 pt-6 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center">
            <p class="text-sm text-gray-500">
                &copy; {{ date('Y') }} Sistem E-Learning Karyawan. All rights reserved.
            </p>
            <div class="flex items-center space-x-4 mt-4 md:mt-0">
                <span class="text-sm text-gray-500">Versi 1.0.0</span>
                <span class="text-gray-300">|</span>
                <span class="text-sm text-gray-500">
                    <i class="fas fa-code"></i> Laravel 13
                </span>
            </div>
        </div>
    </div>
</footer>
