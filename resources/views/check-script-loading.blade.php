@extends('layouts.app')

@section('title', 'Check Script Loading')

@push('styles')
<style>
    .check-section { 
        margin: 20px 0; 
        padding: 20px; 
        border: 1px solid #ccc; 
        border-radius: 8px;
        background: #f9f9f9;
    }
    .check-result {
        background: #e8f5e8;
        border: 1px solid #4caf50;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .check-error {
        background: #ffffff;
        border: 1px solid #f44336;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .code-block {
        background: #f5f5f5;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        font-family: monospace;
        white-space: pre-wrap;
        overflow-x: auto;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Check Script Loading in Recipe Page</h1>
    
    <div class="check-section">
        <h2 class="text-xl font-semibold mb-4">Script Loading Check Results</h2>
        <div id="script-check-results">
            <div class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <p class="mt-2">Checking script loading...</p>
            </div>
        </div>
    </div>
    
    <div class="check-section">
        <h2 class="text-xl font-semibold mb-4">Check Actions</h2>
        <button onclick="runScriptCheck()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
            Run Script Check
        </button>
        <button onclick="clearResults()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Clear Results
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function runScriptCheck() {
        const resultsDiv = document.getElementById('script-check-results');
        resultsDiv.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div><p class="mt-2">Checking script loading...</p></div>';
        
        try {
            // Get the first recipe ID
            const recipesResponse = await fetch('/api/recipes', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });
            
            const recipes = await recipesResponse.json();
            
            if (!recipes || recipes.length === 0) {
                resultsDiv.innerHTML = `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ No Recipes Found</h3>
                        <p>No recipes found to check</p>
                    </div>
                `;
                return;
            }
            
            const recipeId = recipes[0].recipe_id || recipes[0].id;
            const url = `${window.location.origin}/recipe/${recipeId}`;
            
            // Fetch the recipe page
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                },
                credentials: 'include'
            });
            
            const html = await response.text();
            
            let htmlResults = `
                <div class="check-result">
                    <h3 class="font-semibold text-green-700 mb-2">✓ Page Loaded Successfully</h3>
                    <p><strong>URL:</strong> ${url}</p>
                    <p><strong>Status Code:</strong> ${response.status}</p>
                    <p><strong>Check Time:</strong> ${new Date().toLocaleTimeString()}</p>
                </div>
            `;
            
            // Check for recipe.js files
            const recipeJsPatterns = [
                'recipe-DiQlAGcH.js',
                'recipe.js',
                'recipe-',
                'build/assets/recipe-'
            ];
            
            let foundScripts = [];
            let missingScripts = [];
            
            recipeJsPatterns.forEach(pattern => {
                if (html.includes(pattern)) {
                    foundScripts.push(pattern);
                } else {
                    missingScripts.push(pattern);
                }
            });
            
            if (foundScripts.length > 0) {
                htmlResults += `
                    <div class="check-result">
                        <h3 class="font-semibold text-green-700 mb-2">✓ Recipe Scripts Found</h3>
                        <ul class="list-disc list-inside">
                            ${foundScripts.map(script => `<li>${script}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }
            
            if (missingScripts.length > 0) {
                htmlResults += `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ Missing Recipe Scripts</h3>
                        <ul class="list-disc list-inside">
                            ${missingScripts.map(script => `<li>${script}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }
            
            // Extract all script tags
            const scriptMatches = html.match(/<script[^>]*src="([^"]*)"[^>]*><\/script>/g);
            if (scriptMatches) {
                htmlResults += `
                    <div class="check-section">
                        <h3 class="font-semibold mb-2">All Script Files Found:</h3>
                        <div class="code-block">${scriptMatches.join('\n')}</div>
                    </div>
                `;
            } else {
                htmlResults += `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ No Script Tags Found</h3>
                        <p>No script tags found in the HTML</p>
                    </div>
                `;
            }
            
            // Check for Vite assets
            if (html.includes('vite')) {
                htmlResults += `
                    <div class="check-result">
                        <h3 class="font-semibold text-green-700 mb-2">✓ Vite Assets Found</h3>
                        <p>Vite assets are being loaded</p>
                    </div>
                `;
            } else {
                htmlResults += `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ Vite Assets NOT Found</h3>
                        <p>Vite assets are not being loaded</p>
                    </div>
                `;
            }
            
            resultsDiv.innerHTML = htmlResults;
            
        } catch (error) {
            resultsDiv.innerHTML = `
                <div class="check-error">
                    <h3 class="font-semibold text-red-700 mb-2">✗ Script Check Failed</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <div class="code-block">${error.stack}</div>
                </div>
            `;
        }
    }
    
    function clearResults() {
        document.getElementById('script-check-results').innerHTML = '<p class="text-gray-500">Click "Run Script Check" to check script loading.</p>';
    }
    
    // Auto-run check on page load
    document.addEventListener('DOMContentLoaded', function() {
        runScriptCheck();
    });
</script>
@endpush


