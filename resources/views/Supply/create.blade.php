@extends('Template.main')
@section('title', 'Add Supply')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Add Supply</h1>
        <a href="{{ route('supplies.index') }}" class="link-back">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
        </a>
    </div>

    <div class="page-wrapper-md">
        <form method="POST" action="{{ route('supplies.store') }}" class="card-form">
            @csrf

            <div>
                <label class="form-label" for="name">Supply Name</label>
                <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}" required>
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label" for="description">Description</label>
                <textarea name="description" id="description" class="form-textarea" rows="3">{{ old('description') }}</textarea>
                @error('description') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid-2">
                <div>
                    <label class="form-label" for="quantity">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-input" value="{{ old('quantity', 0) }}" min="0" required>
                    @error('quantity') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label" for="unit">Unit</label>
                    <select name="unit" id="unit" class="form-select" required>
                        <option value="piece" {{ old('unit') === 'piece' ? 'selected' : '' }}>Piece</option>
                        <option value="bottle" {{ old('unit') === 'bottle' ? 'selected' : '' }}>Bottle</option>
                        <option value="gallon" {{ old('unit') === 'gallon' ? 'selected' : '' }}>Gallon</option>
                        <option value="liter" {{ old('unit') === 'liter' ? 'selected' : '' }}>Liter</option>
                        <option value="quart" {{ old('unit') === 'quart' ? 'selected' : '' }}>Quart</option>
                        <option value="can" {{ old('unit') === 'can' ? 'selected' : '' }}>Can</option>
                        <option value="box" {{ old('unit') === 'box' ? 'selected' : '' }}>Box</option>
                        <option value="set" {{ old('unit') === 'set' ? 'selected' : '' }}>Set</option>
                        <option value="pair" {{ old('unit') === 'pair' ? 'selected' : '' }}>Pair</option>
                        <option value="roll" {{ old('unit') === 'roll' ? 'selected' : '' }}>Roll</option>
                    </select>
                    @error('unit') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label class="form-label" for="unit_price">Unit Price ($)</label>
                    <input type="number" step="0.01" name="unit_price" id="unit_price" class="form-input" value="{{ old('unit_price', '0.00') }}" min="0" required>
                    @error('unit_price') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label" for="low_stock_threshold">Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" id="low_stock_threshold" class="form-input" value="{{ old('low_stock_threshold', 5) }}" min="0" required>
                    @error('low_stock_threshold') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Add Supply</button>
                <a href="{{ route('supplies.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
