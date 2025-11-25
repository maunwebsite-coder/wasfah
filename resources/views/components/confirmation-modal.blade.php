<!-- resources/views/components/confirmation-modal.blade.php -->
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4" style="backdrop-filter: blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 m-4" style="animation: slideInUp 0.3s ease-out;">
        <div class="text-center">
            <div class="mb-4">
                <i class="fas fa-exclamation-triangle text-5xl text-red-500"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">هل أنت متأكد؟</h3>
            <p class="text-gray-600 mb-6" id="confirmationModalMessage" data-default-message="لا يمكن التراجع عن هذا الإجراء. سيتم حذف العنصر نهائياً.">
                لا يمكن التراجع عن هذا الإجراء. سيتم حذف العنصر نهائياً.
            </p>
            <div class="flex justify-center space-x-4 rtl:space-x-reverse">
                <button id="cancelButton" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg transition-colors">
                    إلغاء
                </button>
                <button id="confirmButton" data-default-label="تأكيد" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                    تأكيد
                </button>
            </div>
        </div>
    </div>
</div>

