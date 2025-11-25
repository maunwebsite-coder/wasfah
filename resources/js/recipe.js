/**
 * @file recipe.js
 * @description هذا الملف مسؤول عن جلب تفاصيل وصفة معينة من الـ API وعرضها في صفحة الوصفة.
 * يتضمن الكود وظائف لتحديث كميات المكونات، عرض الفيديو أو الصورة، ومعالجة خطوات التحضير.
 */

import { processInChunks, queueMicroTask, runOnNextFrame, scheduleIdleTask } from './utils/task-scheduler.js';
import { parseQuantity, formatQuantity } from './utils/quantity.js';


/**
 * تحويل رابط Google Drive إلى رابط مباشر للصورة
 * @param {string} url - رابط الصورة
 * @returns {string} - رابط محول أو الرابط الأصلي
 */
function convertGoogleDriveUrl(url) {
    // إذا كان الرابط من Google Drive، حوله إلى رابط مباشر للصورة
    if (url.includes('drive.google.com/file/d/')) {
        const match = url.match(/\/file\/d\/([a-zA-Z0-9-_]+)/);
        if (match && match[1]) {
            return `https://lh3.googleusercontent.com/d/${match[1]}`;
        }
    }
    
    // إذا كان الرابط من Google Drive مع معاملات إضافية
    if (url.includes('drive.google.com') && url.includes('id=')) {
        const urlParams = new URLSearchParams(new URL(url).search);
        const fileId = urlParams.get('id');
        if (fileId) {
            return `https://lh3.googleusercontent.com/d/${fileId}`;
        }
    }
    
    return url;
}

document.addEventListener("DOMContentLoaded", () => {
	console.log('Recipe.js loaded and DOMContentLoaded fired!');
	// =================================================================================
	// 1. استهداف عناصر الصفحة (DOM Elements)
	// =================================================================================

	const mainContent = document.querySelector('main');
	const recipeTitleEl = document.getElementById('recipe-title');
	const recipeAuthorEl = document.getElementById('recipe-author');
	const recipeDescriptionEl = document.getElementById('recipe-description');
	const recipeUpdatedAtEl = document.getElementById('recipe-updated-at');
	const recipeRatingSummaryEl = document.getElementById('recipe-rating-summary');
	const ingredientsListEl = document.getElementById('ingredients-list');
	const stepsListEl = document.getElementById('steps-list');
	const servingSizeBtns = document.querySelectorAll(".serving-size-btn");
	const infoIcon = document.getElementById("info-icon");
	const infoTooltip = document.getElementById("info-tooltip");
	const madeItCountEl = document.getElementById("made-it-count");
	const saveItCountEl = document.getElementById("save-it-count");
	const mediaContainerEl = document.querySelector('.aspect-video').parentElement; // الحاوية الرئيسية للفيديو أو الصورة
	const recipeImageEl = document.getElementById('recipe-image'); // عنصر الصورة المضاف حديثاً
	const recipeVideoEl = document.getElementById('recipe-video');
	const prepTimeEl = document.getElementById('prep-time');
	const cookTimeEl = document.getElementById('cook-time');
	const servingsEl = document.getElementById('servings');
	// saveRecipeBtn تم نقله إلى RecipeSaveButton class

	const ingredientState = {
		meta: new Map(),
		textNodes: new Map(),
	};
	const hasWorkerSupport = typeof window !== 'undefined' && 'Worker' in window;
	let ingredientWorker = null;
	let latestWorkerRequestId = 0;
	let pendingWorkerRequestId = 0;
	let lastAppliedMultiplier = 1;

	// فحص مبكر لوجود عنصر الخطوات لضمان سلامة الكود
	if (!stepsListEl) {
		console.error("خطأ فادح: لم يتم العثور على العنصر الذي يحمل المعرّف 'steps-list' في ملف HTML.");
		return;
	}

	// =================================================================================
	// 2. دوال العرض والتحديث (Rendering Functions)
	// =================================================================================

	/**
	 * الدالة الرئيسية لعرض جميع بيانات الوصفة في الصفحة.
	 * @param {object} recipe - كائن يحتوي على كافة تفاصيل الوصفة.
	 */
	function renderRecipe(recipe) {
		if (!recipe || !recipe.title) {
			throw new Error('Recipe data is invalid or missing title');
		}

		queueMicroTask(() => {
			document.title = `وصفة: ${recipe.title}`;
			if (recipeTitleEl) recipeTitleEl.textContent = recipe.title;
			if (recipeAuthorEl) recipeAuthorEl.textContent = `بواسطة: ${recipe.author || 'غير معروف'}`;
			if (recipeDescriptionEl) recipeDescriptionEl.textContent = recipe.description || 'لا يوجد وصف متاح';
		});

		runOnNextFrame(() => {
			renderHeaderInfo(recipe);
			renderMedia(recipe.video_url, recipe.image_url, recipe.title);
		});

		scheduleIdleTask(() => renderStats(recipe), { timeout: 200 });

		try {
			renderIngredients(recipe.ingredients || []);
			renderTools(recipe.tools || []);
			renderSteps(recipe.steps || []);

			updateMadeButtonState(recipe.is_made || false);
			scheduleIdleTask(() => processGoogleDriveImages(), { timeout: 500 });
		} catch (error) {
			console.error('Error during recipe rendering:', error);
		}
	}

	/**
	 * يعرض معلومات رأس الصفحة (التاريخ، التقييم، أعداد الحفظ والتجربة).
	 * @param {object} recipe - كائن الوصفة.
	 */
	function renderHeaderInfo(recipe) {
		const updatedDate = new Date(recipe.updated_at).toLocaleDateString('ar-EG', {
			year: 'numeric',
			month: 'long',
			day: 'numeric'
		});
		recipeUpdatedAtEl.textContent = `تم التحديث في ${updatedDate}`;

		// إصلاح عرض التقييم - استخدام الحقل الصحيح
		const avgRating = parseFloat(recipe.interactions_avg_rating || recipe.rating || 0).toFixed(1);
		recipeRatingSummaryEl.innerHTML = `<i class="fas fa-star text-yellow-400 ml-1"></i><span class="ml-2">${avgRating} تقييم</span>`;

		madeItCountEl.innerHTML = `انضم إلى <span class="font-bold text-orange-500 text-2xl">${recipe.made_count || 0}</span> شخص قاموا بتحضيرها!`;
		saveItCountEl.innerHTML = `تم حفظها من قبل <span class="font-bold text-orange-500 text-lg">${recipe.saved_count || 0}</span> شخص!`;
	}

	/**
	 * يحدث عداد الحفظ فورياً في واجهة المستخدم
	 * @param {boolean} isSaved - true إذا تم الحفظ، false إذا تم إلغاء الحفظ
	 */
	function updateSaveCountUI(isSaved) {
		if (!saveItCountEl) return;
		
		const currentCount = parseInt(saveItCountEl.querySelector('span').textContent) || 0;
		const newCount = isSaved ? currentCount + 1 : Math.max(0, currentCount - 1);
		
		saveItCountEl.innerHTML = `تم حفظها من قبل <span class="font-bold text-orange-500 text-lg">${newCount}</span> شخص!`;
		
		// إضافة تأثير بصري
		saveItCountEl.style.transform = 'scale(1.05)';
		saveItCountEl.style.transition = 'transform 0.2s ease-in-out';
		
		setTimeout(() => {
			saveItCountEl.style.transform = 'scale(1)';
		}, 200);
	}

	/**
	 * يحدث حالة زر "لقد قمت بتحضيرها" بناءً على البيانات من API
	 * @param {boolean} isMade - true إذا تم تحضيرها، false إذا لم يتم تحضيرها
	 */
	function updateMadeButtonState(isMade) {
		const madeBtn = document.getElementById('made-recipe-btn');
		const madeBtnText = document.getElementById('made-btn-text');
		
		if (!madeBtn || !madeBtnText) return;
		
		if (isMade) {
			madeBtn.className = 'flex items-center justify-center p-3 rounded-full font-semibold text-white transition-colors bg-green-500 hover:bg-green-600';
			madeBtnText.textContent = 'جربتها!';
			madeBtn.dataset.made = 'true';
		} else {
			madeBtn.className = 'flex items-center justify-center p-3 rounded-full font-semibold text-white transition-colors bg-orange-500 hover:bg-orange-600';
			madeBtnText.textContent = 'لقد جربتها!';
			madeBtn.dataset.made = 'false';
		}
	}

	/**
	 * يعرض الصورة أو الفيديو الخاص بالوصفة ويخفي الآخر.
	 * @param {string|null} videoUrl - رابط فيديو اليوتيوب.
	 * @param {string|null} imageUrl - رابط صورة الوصفة.
	 * @param {string} title - عنوان الوصفة لوصف الصورة (alt text).
	 */
	function renderMedia(videoUrl, imageUrl, title) {
		// إخفاء العناصر مبدئياً
		if(recipeVideoEl) recipeVideoEl.style.display = 'none';
		if(recipeImageEl) recipeImageEl.style.display = 'none';
		if(mediaContainerEl) mediaContainerEl.style.display = 'none';

		// محاولة استخراج معرّف الفيديو من الرابط
		const videoIdMatch = videoUrl ? videoUrl.match(/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/) : null;

		if (videoIdMatch && videoIdMatch[2].length === 11) {
			recipeVideoEl.src = `https://www.youtube.com/embed/${videoIdMatch[2]}`;
			recipeVideoEl.style.display = 'block';
			mediaContainerEl.style.display = 'block';
	} else if (imageUrl) {
		// في حال عدم وجود فيديو صالح، يتم عرض الصورة
		// تحويل رابط Google Drive إذا لزم الأمر
		const convertedImageUrl = convertGoogleDriveUrl(imageUrl);
		recipeImageEl.src = convertedImageUrl;
		recipeImageEl.alt = `صورة لـ ${title}`;
		recipeImageEl.style.display = 'block';
		mediaContainerEl.style.display = 'block';
	}
	}

	/**
	 * يعرض معلومات الوصفة الإضافية (وقت التحضير، الطهي، والكمية).
	 * @param {object} recipe - كائن الوصفة.
	 */
	function renderStats(recipe) {
		prepTimeEl.innerHTML = `<i class="fa-solid fa-clock text-orange-500 ml-2"></i> وقت التحضير: <span class="font-semibold">${recipe.prep_time || 'غير محدد'}</span>`;
		cookTimeEl.innerHTML = `<i class="fa-solid fa-fire text-orange-500 ml-2"></i> وقت الطهي: <span class="font-semibold">${recipe.cook_time || 'غير محدد'}</span>`;
		servingsEl.innerHTML = `<i class="fa-solid fa-utensils text-orange-500 ml-2"></i> الكمية: <span class="font-semibold">${recipe.servings || 'غير محدد'}</span>`;
	}

	/**
	 * يعرض قائمة المكونات في الصفحة.
	 * @param {Array} ingredients - مصفوفة تحتوي على كائنات المكونات.
	 */
	function renderIngredients(ingredients) {
		if (!ingredientsListEl) {
			return;
		}

		ingredientsListEl.innerHTML = '';
		ingredientState.meta.clear();
		ingredientState.textNodes.clear();

		if (!Array.isArray(ingredients) || ingredients.length === 0) {
			ingredientsListEl.innerHTML = '<li>لا توجد مكونات لهذه الوصفة.</li>';
			return;
		}

		let fragment = document.createDocumentFragment();

		processInChunks(ingredients, (ingredient, index) => {
			const quantityText = (ingredient.quantity || '').toString().trim();
			const nameText = ingredient.name || '';
			const fullText = `${quantityText} ${nameText}`.trim() || nameText;
			const ingredientId = `ingredient-${index}`;

			const li = document.createElement('li');
			li.dataset.ingredientId = ingredientId;
			li.className = 'flex items-start gap-2 py-2 text-gray-700';
			li.setAttribute('data-full-text', fullText);

			const textSpan = document.createElement('span');
			textSpan.className = 'full-ingredient-text';
			textSpan.textContent = fullText;

			li.appendChild(textSpan);
			fragment.appendChild(li);

			ingredientState.meta.set(ingredientId, {
				id: ingredientId,
				quantity: quantityText,
				name: nameText,
				fullText,
			});
			ingredientState.textNodes.set(ingredientId, textSpan);
		}, {
			chunkSize: 6,
			onChunkEnd: () => {
				ingredientsListEl.appendChild(fragment);
				fragment = document.createDocumentFragment();
			},
		}).then(() => {
			if (Math.abs(lastAppliedMultiplier - 1) > 0.0001) {
				updateIngredients(lastAppliedMultiplier);
			}
		}).catch((error) => {
			console.error('Failed to render ingredients:', error);
		});
	}

	/**
	 * يعرض المعدات المستخدمة في الوصفة في كروت جميلة
	 * @param {Array} tools - مصفوفة تحتوي على كائنات المعدات
	 */
	function renderTools(tools) {
		const toolsContainerEl = document.getElementById('tools-container');
		
		if (!toolsContainerEl) {
			console.error('tools-container element not found!');
			return;
		}

		const loadingSkeleton = toolsContainerEl.querySelector('#tools-loading-skeleton');
		if (loadingSkeleton) {
			loadingSkeleton.style.display = 'none';
		}

		const emptyState = toolsContainerEl.querySelector('#tools-empty-state');
		if (emptyState) {
			emptyState.style.display = 'none';
		}

		toolsContainerEl.innerHTML = '';
		
		if (!Array.isArray(tools) || tools.length === 0) {
			toolsContainerEl.innerHTML = `
				<div class="text-center text-gray-500 italic col-span-full py-8">
					<i class="fas fa-tools text-4xl text-gray-300 mb-3"></i>
					<p>لا توجد معدات محددة لهذه الوصفة</p>
				</div>
			`;
			return;
		}

		let fragment = document.createDocumentFragment();
		processInChunks(tools, (tool) => {
			fragment.appendChild(createToolCardElement(tool));
		}, {
			chunkSize: 3,
			onChunkEnd: () => {
				toolsContainerEl.appendChild(fragment);
				fragment = document.createDocumentFragment();
			},
		}).catch((error) => {
			console.error('Failed to render tools:', error);
		});
	}

	function createToolCardElement(tool) {
		const toolCard = document.createElement('div');
		toolCard.className = 'tool-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group flex flex-col h-full';

		const defaultImageUrl = '/image/logo.webp';
		const imageUrl = tool?.image_url || defaultImageUrl;
		const ratingValue = Number.isFinite(tool?.rating) ? Number(tool.rating) : 0;
		const priceValue = Number.isFinite(tool?.price) ? Number(tool.price) : 0;

		const stars = Array.from({ length: 5 }, (_, i) =>
			`<i class="fas fa-star ${i < Math.round(ratingValue) ? 'text-yellow-400' : 'text-gray-300'}"></i>`
		).join('');

		const amazonButton = tool?.amazon_url ? `
			<a href="${tool.amazon_url}" 
			   target="_blank"
			   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg text-xs sm:text-sm flex items-center justify-center transition-all duration-300 hover:shadow-lg active:scale-95 group">
				<i class="fab fa-amazon ml-1 sm:ml-2 group-hover:scale-110 transition-transform duration-300"></i>
				<span>متابعة الشراء من Amazon</span>
				<i class="fas fa-external-link-alt mr-1 sm:mr-2 group-hover:translate-x-1 transition-transform duration-300"></i>
			</a>
		` : '';

		const saveButton = (tool?.amazon_url || tool?.affiliate_url) ? `
			<button class="save-for-later-btn w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg text-xs sm:text-sm flex items-center justify-center transition-all duration-300 hover:shadow-lg active:scale-95"
					data-tool-id="${tool?.id}"
					data-tool-name="${tool?.name}"
					data-tool-price="${tool?.price}">
				<i class="fas fa-bookmark ml-1 sm:ml-2"></i>
				<span class="btn-text">حفظ للشراء لاحقاً</span>
				<i class="fas fa-spinner fa-spin ml-1 sm:ml-2 hidden loading-icon"></i>
			</button>
		` : `
			<div class="w-full bg-gray-300 text-gray-600 font-semibold py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg text-xs sm:text-sm flex items-center justify-center">
				<i class="fas fa-exclamation-circle ml-1 sm:ml-2"></i>
				غير متوفر
			</div>
		`;

		toolCard.innerHTML = `
			<div class="relative bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-3 sm:p-4 flex-shrink-0" style="height: 140px;">
				<img src="${imageUrl}" 
					 alt="${tool?.name || 'معدة'}" 
					 class="max-w-full max-h-full object-contain group-hover:scale-105 transition-transform duration-300"
					 width="320"
					 height="200"
					 decoding="async"
					 onerror="this.src='${defaultImageUrl}'; this.alt='صورة افتراضية';">
				<div class="absolute top-2 right-2 bg-orange-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
					${tool?.category || 'معدة'}
				</div>
			</div>

			<div class="p-3 sm:p-4 flex flex-col flex-grow">
				<h3 class="text-xs sm:text-sm font-bold text-gray-900 mb-2 line-clamp-4 leading-tight min-h-[3rem] sm:min-h-[3.5rem]">
					${tool?.name || 'أداة مطبخ'}
				</h3>

				<div class="flex items-center mb-2 sm:mb-3">
					<div class="flex rating-stars text-xs">
						${stars}
					</div>
					<span class="text-xs text-gray-600 mr-2 font-medium">
						${ratingValue}
					</span>
					<span class="text-xs text-gray-500 hidden sm:inline">
						(${Math.floor(Math.random() * 1990) + 10})
					</span>
				</div>

				<div class="text-sm sm:text-lg font-bold text-orange-600 mb-2 sm:mb-3">
					${priceValue.toLocaleString('ar-AE', { minimumFractionDigits: 2 })} درهم إماراتي
				</div>

				<div class="w-full mt-auto space-y-2">
					${amazonButton}
					${saveButton}
				</div>
			</div>
		`;

		return toolCard;
	}



	/**
	 * يعرض خطوات التحضير مع معالجة الأخطاء المحتملة في البيانات.
	 * @param {Array<string>} steps - مصفوفة تحتوي على خطوات التحضير كنصوص.
	 */
	function renderSteps(steps) {
		stepsListEl.innerHTML = ''; // مسح أي خطوات سابقة

		if (!Array.isArray(steps) || steps.length === 0) {
			stepsListEl.innerHTML = '<li>لا توجد خطوات تحضير مدرجة لهذه الوصفة.</li>';
			console.warn("بيانات الخطوات غير صالحة أو فارغة:", steps);
			return;
		}

		let fragment = document.createDocumentFragment();
		processInChunks(steps, (stepText) => {
			if (typeof stepText === 'string' && stepText.trim() !== '') {
				const li = document.createElement('li');
				li.textContent = stepText;
				fragment.appendChild(li);
			}
		}, {
			chunkSize: 5,
			onChunkEnd: () => {
				stepsListEl.appendChild(fragment);
				fragment = document.createDocumentFragment();
			},
		}).catch((error) => {
			console.error('Failed to render steps:', error);
		});
	}

	/**
	 * يعرض رسالة خطأ واضحة للمستخدم في حال فشل تحميل الوصفة.
	 * @param {string} message - رسالة الخطأ المراد عرضها.
	 */
	function displayError(message) {
		mainContent.innerHTML = `<p class="text-center text-red-500 text-xl p-8">${message}</p>`;
	}

	// =================================================================================
	// 3. دوال الأدوات والمساعدة (Utility & Helper Functions)
	// =================================================================================

	function getCsrfToken() {
		const meta = document.querySelector('meta[name="csrf-token"]');
		return meta ? meta.getAttribute('content') : '';
	}

	/**
	 * يعرض رسالة toast للمستخدم
	 * @param {string} message - الرسالة المراد عرضها
	 * @param {string} type - نوع الرسالة (success, error, info, warning)
	 */
	function showToast(message, type = 'info') {
		// إنشاء عنصر toast
		const toast = document.createElement('div');
		toast.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-semibold transform translate-x-full transition-transform duration-300 ${
			type === 'success' ? 'bg-green-500' : 
			type === 'error' ? 'bg-red-500' : 
			type === 'warning' ? 'bg-yellow-500' :
			'bg-blue-500'
		}`;
		toast.textContent = message;
		
		// إضافة للصفحة
		document.body.appendChild(toast);
		
		// إظهار الرسالة
		setTimeout(() => {
			toast.classList.remove('translate-x-full');
		}, 100);
		
		// إخفاء الرسالة بعد 3 ثواني
		setTimeout(() => {
			toast.classList.add('translate-x-full');
			setTimeout(() => {
				toast.remove();
			}, 300);
		}, 3000);
	}

	/**
	 * يحلل القيم النصية التي قد تكون أرقامًا صحيحة أو كسورًا.
	 * @param {string} value - النص المراد تحليله (مثل "1" أو "1/2").
	 * @returns {number|null} - القيمة الرقمية أو null إذا لم تكن رقمًا صالحًا.
	 */
	function ensureIngredientWorker() {
		if (!hasWorkerSupport) {
			return null;
		}

		if (!ingredientWorker) {
			try {
				ingredientWorker = new Worker(
					new URL('./workers/ingredient-resizer.worker.js', import.meta.url),
					{ type: 'module' }
				);

				ingredientWorker.onmessage = (event) => {
					const { requestId, data } = event.data || {};
					if (!requestId || requestId !== pendingWorkerRequestId) {
						return;
					}
					applyScaledQuantities(data);
				};

				ingredientWorker.onerror = (error) => {
					console.error('Ingredient worker error:', error);
					ingredientWorker.terminate();
					ingredientWorker = null;
				};
			} catch (error) {
				console.error('Failed to initialize ingredient worker:', error);
				ingredientWorker = null;
			}
		}

		return ingredientWorker;
	}

	function applyScaledQuantities(updates) {
		if (!Array.isArray(updates) || updates.length === 0) {
			return;
		}

		updates.forEach(({ id, text }) => {
			if (!id) {
				return;
			}

			const node = ingredientState.textNodes.get(id);
			if (node) {
				node.textContent = text;
			}
		});
	}

	function scaleIngredientsSynchronously(multiplier) {
		const updates = Array.from(ingredientState.meta.values()).map((meta) => {
			const { amount, unit } = parseQuantity(meta.quantity);
			if (amount === null) {
				return { id: meta.id, text: meta.fullText };
			}

			const scaledAmount = amount * multiplier;
			const displayQuantity = formatQuantity(scaledAmount);
			const parts = [displayQuantity, unit, meta.name].filter((part) => part && part.toString().trim().length > 0);

			return {
				id: meta.id,
				text: parts.join(' ').trim() || meta.fullText,
			};
		});

		applyScaledQuantities(updates);
	}

	/**
	 * يحدّث كميات المكونات بناءً على المضاعف الذي يختاره المستخدم.
	 * @param {number} multiplier - معامل الضرب (مثل 0.5 لنصف الكمية).
	 */
	function updateIngredients(multiplier) {
		if (!ingredientsListEl || ingredientState.meta.size === 0) {
			return;
		}

		const numericMultiplier = Number(multiplier);
		if (!Number.isFinite(numericMultiplier) || numericMultiplier <= 0) {
			return;
		}

		lastAppliedMultiplier = numericMultiplier;

		if (Math.abs(numericMultiplier - 1) < 0.0001) {
			ingredientState.meta.forEach((meta, id) => {
				const node = ingredientState.textNodes.get(id);
				if (node) {
					node.textContent = meta.fullText;
				}
			});
			return;
		}

		if (!hasWorkerSupport) {
			scaleIngredientsSynchronously(numericMultiplier);
			return;
		}

		const worker = ensureIngredientWorker();
		if (!worker) {
			scaleIngredientsSynchronously(numericMultiplier);
			return;
		}

		const requestId = ++latestWorkerRequestId;
		pendingWorkerRequestId = requestId;

		const payload = Array.from(ingredientState.meta.values());
		worker.postMessage({
			requestId,
			multiplier: numericMultiplier,
			ingredients: payload,
		});
	}

	/**
	 * تهيئة بيانات زر الحفظ (تم الاستعاضة عنها بـ RecipeSaveButton)
	 * @deprecated استخدم RecipeSaveButton بدلاً من هذه الدالة
	 */
	function initializeSaveButtonState(recipe) {
		console.log('initializeSaveButtonState is deprecated. Using RecipeSaveButton instead.');
		// لا حاجة للتهيئة هنا - يتم التعامل مع زر الحفظ بواسطة RecipeSaveButton
	}


	// =================================================================================
	// 4. تهيئة الأحداث والمنطق الرئيسي (Initialization & Main Logic)
	// =================================================================================

	/**
	 * يضيف مستمعي الأحداث لأزرار التحكم في حجم الحصة.
	 */
	function initializeServingSizeControls() {
		servingSizeBtns.forEach((btn) => {
			btn.addEventListener("click", function() {
				updateIngredients(parseFloat(this.dataset.multiplier));
				servingSizeBtns.forEach((b) => b.classList.remove("bg-orange-500", "text-white"));
				this.classList.add("bg-orange-500", "text-white");
			});
		});
	}

	/**
	 * يهيئ سلوك الظهور والإخفاء لتلميح المعلومات.
	 */
	function initializeInfoTooltip() {
		if (!infoIcon || !infoTooltip) return;
		infoIcon.addEventListener("click", (e) => {
			e.stopPropagation();
			infoTooltip.classList.toggle("hidden");
		});
		document.addEventListener("click", (e) => {
			if (!infoIcon.contains(e.target) && !infoTooltip.contains(e.target)) {
				infoTooltip.classList.add("hidden");
			}
		});
	}

	/**
	 * يجلب تفاصيل الوصفة من البيانات المحملة مسبقاً في الصفحة
	 */
	function fetchRecipeDetails() {
		// الحصول على بيانات الوصفة من العناصر الموجودة في الصفحة
		const recipeTitle = document.getElementById('recipe-title')?.textContent;
		const recipeAuthor = document.getElementById('recipe-author')?.textContent;
		const recipeDescription = document.getElementById('recipe-description')?.textContent;
		
		if (!recipeTitle) {
			displayError('لم يتم العثور على بيانات الوصفة في الصفحة.');
			return;
		}

		// استخراج معرف الوصفة من الزر
		const saveButton = document.getElementById('save-recipe-page-btn');
		const recipeId = saveButton?.dataset.recipeId;
		const isSaved = saveButton?.dataset.saved === 'true';
		
		if (!recipeId) {
			displayError('لم يتم العثور على معرف الوصفة.');
			return;
		}

		// إنشاء كائن الوصفة من البيانات الموجودة في الصفحة
		const recipeData = {
			recipe_id: parseInt(recipeId),
			title: recipeTitle,
			author: recipeAuthor?.replace('بواسطة: ', '') || 'غير معروف',
			description: recipeDescription || 'لا يوجد وصف متاح',
			is_saved: isSaved,
			// يمكن إضافة المزيد من البيانات حسب الحاجة
			ingredients: [],
			steps: [],
			tools: [],
			interactions_avg_rating: 0,
			made_count: 0,
			saved_count: 0,
			prep_time: 0,
			cook_time: 0,
			servings: 0,
			updated_at: new Date().toISOString()
		};

		console.log('Using server-side recipe data:', recipeData);
		renderRecipe(recipeData);
	}

	/**
	 * معالجة روابط Google Drive في صفحة الوصفة
	 */
	function processGoogleDriveImages() {
		// معالجة جميع الصور التي تحتوي على روابط Google Drive
		const images = document.querySelectorAll('img[src*="drive.google.com"]');
		images.forEach(function(img) {
			const originalSrc = img.src;
			const convertedSrc = convertGoogleDriveUrl(originalSrc);
			if (convertedSrc !== originalSrc) {
				img.src = convertedSrc;
			}
		});
	}

	/**
	 * يهيئ وظيفة طباعة الوصفة
	 */
	function initializePrintFunctionality() {
		const printBtn = document.getElementById('print-recipe-btn');
		if (!printBtn) return;

		printBtn.addEventListener('click', function() {
			// إنشاء نافذة طباعة جديدة
			const printWindow = window.open('', '_blank');
			
			// الحصول على بيانات الوصفة
			const recipeTitle = document.getElementById('recipe-title').textContent || 'وصفة';
			const recipeAuthor = document.getElementById('recipe-author').textContent || 'غير معروف';
			const recipeDescription = document.getElementById('recipe-description').textContent || '';
			const prepTime = document.getElementById('prep-time').textContent || 'غير محدد';
			const cookTime = document.getElementById('cook-time').textContent || 'غير محدد';
			const servings = document.getElementById('servings').textContent || 'غير محدد';
			
			// الحصول على المكونات
			const ingredients = Array.from(document.querySelectorAll('#ingredients-list li')).map(li => li.textContent.trim()).filter(text => text);
			
			// الحصول على المعدات
			const tools = Array.from(document.querySelectorAll('#tools-container h3')).map(h3 => h3.textContent.trim()).filter(text => text);
			
			// الحصول على خطوات التحضير
			const steps = Array.from(document.querySelectorAll('#steps-list li')).map(li => li.textContent.trim()).filter(text => text);
			
			// إنشاء HTML كامل للطباعة
			const printHTML = `
				<!DOCTYPE html>
				<html dir="rtl" lang="ar">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>طباعة الوصفة - ${recipeTitle}</title>
					<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
					<style>
						* {
							margin: 0;
							padding: 0;
							box-sizing: border-box;
						}
						
						body {
							font-family: 'Arial', 'Tahoma', 'Segoe UI', sans-serif;
							line-height: 1.6;
							color: #333;
							background: white;
							padding: 20px;
							direction: rtl;
						}
						
						.print-header {
							text-align: center;
							margin-bottom: 30px;
							border-bottom: 3px solid #0f4c73;
							padding-bottom: 20px;
						}
						
						.print-title {
							font-size: 2.8em;
							color: #0f4c73;
							margin-bottom: 15px;
							font-weight: bold;
							text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
						}
						
						.print-author {
							font-size: 1.3em;
							color: #666;
							margin-bottom: 15px;
							font-weight: 500;
						}
						
						.print-description {
							font-size: 1.2em;
							color: #555;
							line-height: 1.8;
							margin-bottom: 20px;
							background: #f8f9fa;
							padding: 15px;
							border-radius: 8px;
							border-right: 4px solid #0f4c73;
						}
						
						.print-section {
							margin-bottom: 35px;
							page-break-inside: avoid;
						}
						
						.print-section h2 {
							font-size: 2em;
							color: #0f4c73;
							margin-bottom: 20px;
							border-bottom: 2px solid #0f4c73;
							padding-bottom: 8px;
							text-align: center;
						}
						
						.print-info {
							display: flex;
							justify-content: space-around;
							background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
							padding: 20px;
							border-radius: 12px;
							margin-bottom: 25px;
							box-shadow: 0 2px 4px rgba(0,0,0,0.1);
						}
						
						.print-info-item {
							text-align: center;
							font-size: 1.2em;
							font-weight: 600;
							color: #333;
						}
						
						.print-info-item i {
							color: #0f4c73;
							margin-left: 8px;
							font-size: 1.3em;
						}
						
						.print-ingredients {
							background: #f8f9fa;
							padding: 25px;
							border-radius: 12px;
							border: 1px solid #dee2e6;
						}
						
						.print-ingredients ul {
							list-style: none;
							padding: 0;
						}
						
						.print-ingredients li {
							padding: 12px 0;
							border-bottom: 1px solid #dee2e6;
							font-size: 1.2em;
							position: relative;
							padding-right: 20px;
						}
						
						.print-ingredients li:before {
							content: "•";
							color: #0f4c73;
							font-weight: bold;
							position: absolute;
							right: 0;
							font-size: 1.5em;
						}
						
						.print-ingredients li:last-child {
							border-bottom: none;
						}
						
						.print-steps {
							background: white;
							padding: 25px;
							border: 1px solid #dee2e6;
							border-radius: 12px;
						}
						
						.print-steps ol {
							padding-right: 25px;
							counter-reset: step-counter;
						}
						
						.print-steps li {
							padding: 15px 0;
							font-size: 1.2em;
							line-height: 1.8;
							counter-increment: step-counter;
							position: relative;
							border-bottom: 1px solid #f0f0f0;
						}
						
						.print-steps li:last-child {
							border-bottom: none;
						}
						
						.print-steps li:before {
							content: counter(step-counter);
							position: absolute;
							right: -25px;
							top: 15px;
							background: #0f4c73;
							color: white;
							width: 25px;
							height: 25px;
							border-radius: 50%;
							display: flex;
							align-items: center;
							justify-content: center;
							font-weight: bold;
							font-size: 0.9em;
						}
						
						.print-footer {
							text-align: center;
							margin-top: 50px;
							padding-top: 20px;
							border-top: 2px solid #0f4c73;
							color: #666;
							font-size: 1em;
							background: #f8f9fa;
							padding: 20px;
							border-radius: 8px;
						}
						
						.print-footer p {
							margin: 5px 0;
						}
						
						@media print {
							body {
								padding: 15px;
								font-size: 12pt;
							}
							
							.print-section {
								page-break-inside: avoid;
								margin-bottom: 25px;
							}
							
							.print-title {
								font-size: 2.2em;
							}
							
							.print-info {
								page-break-inside: avoid;
							}
							
							.print-ingredients,
							.print-steps {
								page-break-inside: avoid;
							}
							
							.print-steps li {
								page-break-inside: avoid;
							}
						}
					</style>
				</head>
				<body>
					<div class="print-header">
						<h1 class="print-title">${recipeTitle}</h1>
						<div class="print-author">${recipeAuthor}</div>
						${recipeDescription ? `<div class="print-description">${recipeDescription}</div>` : ''}
					</div>
					
					<div class="print-section">
						<div class="print-info">
							<div class="print-info-item">
								<i class="fas fa-clock"></i>
								${prepTime}
							</div>
							<div class="print-info-item">
								<i class="fas fa-fire"></i>
								${cookTime}
							</div>
							<div class="print-info-item">
								<i class="fas fa-utensils"></i>
								${servings}
							</div>
						</div>
					</div>
					
					<div class="print-section print-ingredients">
						<h2>المكونات</h2>
						<ul>
							${ingredients.map(ingredient => `<li>${ingredient}</li>`).join('')}
						</ul>
					</div>
					
					${tools.length > 0 ? `
					<div class="print-section print-ingredients">
						<h2>المعدات المستخدمة</h2>
						<ul>
							${tools.map(tool => `<li>${tool}</li>`).join('')}
						</ul>
					</div>
					` : ''}
					
					<div class="print-section print-steps">
						<h2>خطوات التحضير</h2>
						<ol>
							${steps.map(step => `<li>${step}</li>`).join('')}
						</ol>
					</div>
					
					<div class="print-footer">
						<p><strong>تم طباعة هذه الوصفة من موقع وصفة</strong></p>
						<p>تاريخ الطباعة: ${new Date().toLocaleDateString('ar-EG', {
							year: 'numeric',
							month: 'long',
							day: 'numeric',
							weekday: 'long'
						})}</p>
						<p>وقت الطباعة: ${new Date().toLocaleTimeString('ar-EG')}</p>
					</div>
				</body>
				</html>
			`;
			
			// كتابة المحتوى في النافذة الجديدة
			printWindow.document.write(printHTML);
			printWindow.document.close();
			
			// انتظار تحميل الصفحة ثم فتح نافذة الطباعة
			printWindow.onload = function() {
				printWindow.focus();
				printWindow.print();
				// إغلاق النافذة بعد الطباعة (اختياري)
				// printWindow.close();
			};
		});
	}

	/**
	 * الدالة الرئيسية التي تبدأ تشغيل كل شيء.
	 */
	function init() {
		initializeServingSizeControls();
		initializeInfoTooltip();
		initializePrintFunctionality();
		
		// ملاحظة: زر الحفظ يتم التعامل معه الآن بواسطة RecipeSaveButton
		
		fetchRecipeDetails();
		processGoogleDriveImages();
	}

	/**
	 * تهيئة زر الحفظ من البيانات الموجودة في الصفحة
	 * @deprecated يتم التعامل مع زر الحفظ بواسطة RecipeSaveButton
	 */
	function initializeSaveButtonFromPage() {
		console.log('Save button initialization is now handled by RecipeSaveButton class.');
		// لا حاجة للتهيئة هنا - يتم التعامل مع زر الحفظ بواسطة RecipeSaveButton
	}

	init(); // تشغيل التطبيق
});



