@extends('layouts.app')

@section('title', 'Test Recipe JavaScript')

@push('styles')
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .debug-section { margin: 20px 0; padding: 20px; border: 1px solid #ccc; }
    .tool-card {
        background: linear-gradient(135deg, #f4f6ff 0%, #c8d6ff 100%);
        border: 1px solid #16c3ff;
        border-radius: 12px;
        padding: 1.5rem;
        margin: 10px;
        display: inline-block;
        width: 200px;
    }
    .tool-icon {
        width: 3rem;
        height: 3rem;
        background: linear-gradient(135deg, #0819ff, #16c3ff);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    .tool-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
        text-align: center;
    }
    #tools-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1>Test Recipe JavaScript</h1>
    
    <div class="debug-section">
        <h2>Test Tools Rendering</h2>
        <div id="tools-container">
            <!-- Tools will be rendered here -->
        </div>
        <button onclick="testRenderTools()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
            Test Render Tools
        </button>
        <button onclick="testAPI()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Test API Call
        </button>
    </div>
    
    <div class="debug-section">
        <h2>Console Output</h2>
        <div id="console-output" class="bg-gray-100 p-4 rounded min-h-[200px] font-mono text-sm"></div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Load the actual recipe.js file -->
<script src="{{ asset('build/assets/recipe-C2GwO11r.js') }}"></script>

<script>
    // Override console.log to also display in the page
    const originalLog = console.log;
    const originalError = console.error;
    const consoleOutput = document.getElementById('console-output');
    
    function addToConsole(message, type = 'log') {
        const div = document.createElement('div');
        div.style.color = type === 'error' ? 'red' : 'black';
        div.textContent = `[${type.toUpperCase()}] ${message}`;
        consoleOutput.appendChild(div);
    }
    
    console.log = function(...args) {
        originalLog.apply(console, args);
        addToConsole(args.join(' '), 'log');
    };
    
    console.error = function(...args) {
        originalError.apply(console, args);
        addToConsole(args.join(' '), 'error');
    };

    // Test data
    const testTools = [
        "Zophen Olive Oil Sprayer for Cooking",
        "NALACAL Mini Pudding Pan Set",
        "Bukela Mini Tart Pan Set"
    ];

    function renderTools(tools) {
        console.log('renderTools called with:', tools);
        const toolsContainerEl = document.getElementById('tools-container');
        console.log('toolsContainerEl found:', toolsContainerEl);
        
        if (!toolsContainerEl) {
            console.error('tools-container element not found!');
            return;
        }

        toolsContainerEl.innerHTML = '';
        
        if (Array.isArray(tools) && tools.length > 0) {
            console.log('Rendering', tools.length, 'tools');
            tools.forEach((tool, index) => {
                console.log(`Rendering tool ${index + 1}:`, tool);
                const toolCard = document.createElement('div');
                toolCard.className = 'tool-card';
                toolCard.innerHTML = `
                    <div class="text-center">
                        <div class="tool-icon">
                            <i class="fas fa-tools" style="color: white; font-size: 1.2rem;">🔧</i>
                        </div>
                        <h3 class="tool-name">${tool}</h3>
                    </div>
                `;
                toolsContainerEl.appendChild(toolCard);
            });
        } else {
            console.log('No tools to render, showing empty message');
            toolsContainerEl.innerHTML = `
                <div style="text-align: center; color: #666; padding: 20px;">
                    <i class="fas fa-tools" style="font-size: 3rem; color: #ccc; margin-bottom: 10px;">🔧</i>
                    <p>لا توجد معدات محددة لهذه الوصفة</p>
                </div>
            `;
        }
    }

    function testRenderTools() {
        console.log('Testing renderTools with test data...');
        renderTools(testTools);
    }

    async function testAPI() {
        console.log('Testing API call...');
        try {
            const response = await fetch('/api/recipes/1', { credentials: 'include' });
            console.log('API Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`API Error: ${response.status}`);
            }
            
            const recipeData = await response.json();
            console.log('Recipe data received:', recipeData);
            console.log('Recipe tools:', recipeData.tools);
            
            if (recipeData.tools && Array.isArray(recipeData.tools)) {
                renderTools(recipeData.tools);
            } else {
                console.log('No tools in API response');
                renderTools([]);
            }
        } catch (error) {
            console.error('API Error:', error);
        }
    }

    // Auto-test on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded, running tests...');
        testRenderTools();
        setTimeout(() => {
            testAPI();
        }, 1000);
    });
</script>
@endpush





