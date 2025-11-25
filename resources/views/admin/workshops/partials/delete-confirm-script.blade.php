@push('scripts')
    @once
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endonce
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function decodeHtml(html) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = html;
            return textarea.value;
        }

        function attachWorkshopDeletionConfirmations() {
            const deleteForms = document.querySelectorAll('.delete-workshop-form');

            deleteForms.forEach(function(form) {
                if (form.dataset.swalBound === 'true') {
                    return;
                }

                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    const rawTitle = form.dataset.workshopTitle || 'هذه الورشة';
                    const workshopTitle = decodeHtml(rawTitle);

                    Swal.fire({
                        title: 'تأكيد الحذف',
                        html: `
                            <div class="text-center">
                                <div class="swal2-icon-wrapper">
                                    <i class="fas fa-trash-alt"></i>
                                </div>
                                <p class="swal2-text-primary">هل أنت متأكد من رغبتك في حذف هذه الورشة؟</p>
                                <p class="swal2-text-highlight">"${workshopTitle}"</p>
                                <p class="swal2-text-muted">لا يمكن التراجع عن هذا الإجراء بعد تنفيذه.</p>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        reverseButtons: true,
                        focusCancel: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: '<span class="swal2-button-content"><i class="fas fa-trash-alt"></i> نعم، احذف الورشة</span>',
                        cancelButtonText: '<span class="swal2-button-content"><i class="fas fa-times"></i> تراجع</span>',
                        customClass: {
                            popup: 'swal2-popup-arabic',
                            title: 'swal2-title-arabic',
                            content: 'swal2-content-arabic',
                            confirmButton: 'swal2-confirm-arabic',
                            cancelButton: 'swal2-cancel-arabic'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });

                form.dataset.swalBound = 'true';
            });
        }

        function waitForSwal(attempts = 0) {
            if (typeof Swal === 'undefined') {
                if (attempts < 20) {
                    setTimeout(() => waitForSwal(attempts + 1), 75);
                } else {
                    console.error('SweetAlert2 failed to load.');
                }
                return;
            }

            attachWorkshopDeletionConfirmations();
        }

        waitForSwal();
    });
    </script>
@endpush

