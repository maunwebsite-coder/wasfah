@extends('layouts.app')

@section('title', 'Check Tools')

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
        background: #eaf3f6;
        border: 1px solid #f44336;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .check-warning {
        background: #fff3e0;
        border: 1px solid #0fa289;
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
        max-height: 300px;
    }
    .recipe-info {
        background: #e3f2fd;
        border: 1px solid #2196f3;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .tool-info {
        background: #f3e5f5;
        border: 1px solid #9c27b0;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Check Recipes with Tools</h1>
    
    <div class="check-section">
        <h2 class="text-xl font-semibold mb-4">Recipes with Tools</h2>
        <div id="recipes-check-results">
            <div class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <p class="mt-2">Loading recipes with tools...</p>
            </div>
        </div>
    </div>
    
    <div class="check-section">
        <h2 class="text-xl font-semibold mb-4">Available Tools</h2>
        <div id="tools-check-results">
            <div class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <p class="mt-2">Loading available tools...</p>
            </div>
        </div>
    </div>
    
    <div class="check-section">
        <h2 class="text-xl font-semibold mb-4">Sample Recipe Data</h2>
        <div id="sample-recipe-results">
            <div class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <p class="mt-2">Loading sample recipe data...</p>
            </div>
        </div>
    </div>
    
    <div class="check-section">
        <h2 class="text-xl font-semibold mb-4">Check Actions</h2>
        <button onclick="runAllChecks()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
            Run All Checks
        </button>
        <button onclick="checkRecipes()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2">
            Check Recipes Only
        </button>
        <button onclick="checkTools()" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mr-2">
            Check Tools Only
        </button>
        <button onclick="clearResults()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Clear Results
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function checkRecipes() {
        const resultsDiv = document.getElementById('recipes-check-results');
        resultsDiv.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div><p class="mt-2">Loading recipes with tools...</p></div>';
        
        try {
            const response = await fetch('/api/recipes', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });
            
            const recipes = await response.json();
            
            if (!recipes || recipes.length === 0) {
                resultsDiv.innerHTML = `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ No Recipes Found</h3>
                        <p>No recipes found in the database</p>
                    </div>
                `;
                return;
            }
            
            const recipesWithTools = recipes.filter(recipe => recipe.tools && recipe.tools.length > 0);
            
            let html = `
                <div class="check-result">
                    <h3 class="font-semibold text-green-700 mb-2">✓ Recipes Found</h3>
                    <p><strong>Total Recipes:</strong> ${recipes.length}</p>
                    <p><strong>Recipes with Tools:</strong> ${recipesWithTools.length}</p>
                </div>
            `;
            
            if (recipesWithTools.length > 0) {
                html += `
                    <div class="check-section">
                        <h3 class="font-semibold mb-2">Recipes with Tools:</h3>
                        <div class="space-y-4">
                `;
                
                recipesWithTools.forEach(recipe => {
                    html += `
                        <div class="recipe-info">
                            <h4 class="font-semibold text-blue-700 mb-2">Recipe ID: ${recipe.recipe_id || recipe.id}</h4>
                            <p><strong>Title:</strong> ${recipe.title}</p>
                            <p><strong>Tools Count:</strong> ${recipe.tools.length}</p>
                            <div class="mt-2">
                                <strong>Tools:</strong>
                                <ul class="list-disc list-inside mt-1">
                                    ${recipe.tools.map(tool => `<li>${tool}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="check-warning">
                        <h3 class="font-semibold text-orange-700 mb-2">⚠ No Recipes with Tools</h3>
                        <p>No recipes found with tools assigned</p>
                    </div>
                `;
            }
            
            resultsDiv.innerHTML = html;
            
        } catch (error) {
            resultsDiv.innerHTML = `
                <div class="check-error">
                    <h3 class="font-semibold text-red-700 mb-2">✗ Recipes Check Failed</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <div class="code-block">${error.stack}</div>
                </div>
            `;
        }
    }
    
    async function checkTools() {
        const resultsDiv = document.getElementById('tools-check-results');
        resultsDiv.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div><p class="mt-2">Loading available tools...</p></div>';
        
        try {
            const response = await fetch('/api/tools', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });
            
            const tools = await response.json();
            
            if (!tools || tools.length === 0) {
                resultsDiv.innerHTML = `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ No Tools Found</h3>
                        <p>No tools found in the database</p>
                    </div>
                `;
                return;
            }
            
            const activeTools = tools.filter(tool => tool.is_active);
            
            let html = `
                <div class="check-result">
                    <h3 class="font-semibold text-green-700 mb-2">✓ Tools Found</h3>
                    <p><strong>Total Tools:</strong> ${tools.length}</p>
                    <p><strong>Active Tools:</strong> ${activeTools.length}</p>
                </div>
            `;
            
            if (activeTools.length > 0) {
                html += `
                    <div class="check-section">
                        <h3 class="font-semibold mb-2">Active Tools:</h3>
                        <div class="space-y-2">
                `;
                
                activeTools.forEach(tool => {
                    html += `
                        <div class="tool-info">
                            <p><strong>Tool ID:</strong> ${tool.id}</p>
                            <p><strong>Name:</strong> ${tool.name}</p>
                            ${tool.description ? `<p><strong>Description:</strong> ${tool.description}</p>` : ''}
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="check-warning">
                        <h3 class="font-semibold text-orange-700 mb-2">⚠ No Active Tools</h3>
                        <p>No active tools found in the database</p>
                    </div>
                `;
            }
            
            resultsDiv.innerHTML = html;
            
        } catch (error) {
            resultsDiv.innerHTML = `
                <div class="check-error">
                    <h3 class="font-semibold text-red-700 mb-2">✗ Tools Check Failed</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <div class="code-block">${error.stack}</div>
                </div>
            `;
        }
    }
    
    async function checkSampleRecipe() {
        const resultsDiv = document.getElementById('sample-recipe-results');
        resultsDiv.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div><p class="mt-2">Loading sample recipe data...</p></div>';
        
        try {
            const response = await fetch('/api/recipes', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });
            
            const recipes = await response.json();
            
            if (!recipes || recipes.length === 0) {
                resultsDiv.innerHTML = `
                    <div class="check-error">
                        <h3 class="font-semibold text-red-700 mb-2">✗ No Sample Recipe Found</h3>
                        <p>No recipes found to show sample data</p>
                    </div>
                `;
                return;
            }
            
            const sampleRecipe = recipes[0];
            
            let html = `
                <div class="check-result">
                    <h3 class="font-semibold text-green-700 mb-2">✓ Sample Recipe Data</h3>
                    <p><strong>Recipe ID:</strong> ${sampleRecipe.recipe_id || sampleRecipe.id}</p>
                    <p><strong>Title:</strong> ${sampleRecipe.title}</p>
                    <p><strong>Tools Count:</strong> ${sampleRecipe.tools ? sampleRecipe.tools.length : 0}</p>
                </div>
            `;
            
            if (sampleRecipe.tools && sampleRecipe.tools.length > 0) {
                html += `
                    <div class="check-section">
                        <h3 class="font-semibold mb-2">Sample Recipe Tools:</h3>
                        <div class="code-block">${JSON.stringify(sampleRecipe.tools, null, 2)}</div>
                    </div>
                `;
            } else {
                html += `
                    <div class="check-warning">
                        <h3 class="font-semibold text-orange-700 mb-2">⚠ No Tools in Sample Recipe</h3>
                        <p>Sample recipe has no tools assigned</p>
                    </div>
                `;
            }
            
            // Show full recipe data
            html += `
                <div class="check-section">
                    <h3 class="font-semibold mb-2">Full Sample Recipe Data:</h3>
                    <div class="code-block">${JSON.stringify(sampleRecipe, null, 2)}</div>
                </div>
            `;
            
            resultsDiv.innerHTML = html;
            
        } catch (error) {
            resultsDiv.innerHTML = `
                <div class="check-error">
                    <h3 class="font-semibold text-red-700 mb-2">✗ Sample Recipe Check Failed</h3>
                    <p><strong>Error:</strong> ${error.message}</p>
                    <div class="code-block">${error.stack}</div>
                </div>
            `;
        }
    }
    
    async function runAllChecks() {
        await checkRecipes();
        await checkTools();
        await checkSampleRecipe();
    }
    
    function clearResults() {
        document.getElementById('recipes-check-results').innerHTML = '<p class="text-gray-500">Click "Run All Checks" to check recipes.</p>';
        document.getElementById('tools-check-results').innerHTML = '<p class="text-gray-500">Click "Run All Checks" to check tools.</p>';
        document.getElementById('sample-recipe-results').innerHTML = '<p class="text-gray-500">Click "Run All Checks" to check sample recipe.</p>';
    }
    
    // Auto-run checks on page load
    document.addEventListener('DOMContentLoaded', function() {
        runAllChecks();
    });
</script>
@endpush




