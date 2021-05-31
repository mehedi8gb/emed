<div class="form-group row">
    <label class="col-md-2 col-form-label">
        {{translate('Job Title:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-3">
        <input type="text" placeholder="{{translate('Job Title')}}" onkeyup="makeSlug(this.value)" id="job_title" name="job_title" value="{{old('job_title')}}" class="form-control" >
            @error('job_title')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>

          {{-- company --}}

    <label class="col-md-2 col-form-label">
        {{translate('Company Name:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-3">
        <input type="text" placeholder="{{translate('Enter Your Company Name')}}"  id="company" name="company" value="{{old('company')}}" class="form-control" >
            @error('company')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
      {{-- end company --}}
</div>




<div class="form-group row" id="category">
    <label class="col-md-2 col-from-label">
        {{translate('Category:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-3">
        <select class="form-control aiz-selectpicker" name="category_id" id="category_id" data-live-search="true" >
            <option disabled selected value="">select category</option>
  @foreach ($job_category as $category)
   <option value="{{ $category->id }}" {{  old('category_id') ==  $category->id ? 'selected' : '' }}> {{ $category->category_name }}</option>
            @endforeach
        </select>
        @error('category_id')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>

     {{-- location --}}

    <label class="col-md-2 col-from-label">
        {{translate('Location:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-3">
        <select class="form-control aiz-selectpicker" name="location_id" id="location_id" data-live-search="true" >
            <option value="" selected disabled>select location</option>
            @foreach ($job_locations as $item )
    <option value="{{ $item->id }}" {{  old('location_id') ==  $item->id ? 'selected' : '' }}>{{$item->location}}</option>
            @endforeach
        </select>
        @error('location')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>


    {{--End location --}}
</div>



{{-- education --}}
<div class="form-group row">
    <label class="col-md-2 col-form-label">
        {{translate('educational Status:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-3">
        <input type="text" placeholder="{{translate('Your educational status')}}"  id="education" name="education" value="{{old('education')}}" class="form-control" >
            @error('education')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>

{{-- end education --}}

{{-- experience --}}

    <label class="col-md-2 col-form-label">
        {{translate('Experience:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-3">
        <input type="text" placeholder="{{translate('Your experience')}}"  id="experience" name="experience" value="{{old('experience')}}" class="form-control" >
            @error('experience')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>
</div>
{{-- end experience --}}

     {{-- Gender --}}

<div class="form-group row">
        <label class="col-md-2 col-form-label">
            {{translate('Gender:')}}
            <span class="text-danger">*</span>
        </label>
        <div class="col-md-3">
            <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" id="customControlValidation2" value="1" name="gender"  {{  old('gender') ==  1 ? 'checked' : '' }}>
                <label class="custom-control-label" for="customControlValidation2">Male</label>
                <div class="invalid-feedback">Please check one option</div>
              </div>
              <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" id="customControlValidation3" value="2" name="gender"   {{  old('gender') ==  2 ? 'checked' : '' }}>
                <label class="custom-control-label" for="customControlValidation3">Females</label>
                <div class="invalid-feedback">Please check one option</div>
              </div>
              <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" id="customControlValidation4" value="3" name="gender"  {{  old('gender') ==  3 ? 'checked' : '' }}>
                <label class="custom-control-label" for="customControlValidation4">Both's</label>
                <div class="invalid-feedback">Please check one option</div>
              </div>

         </div>


   {{-- end Gender --}}


   {{-- salary --}}

    <label class="col-md-1 col-form-label">
        {{translate('Salary:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-2">
        <input type="text" placeholder="{{translate('salary')}}"  id="salary" name="salary" value="{{old('salary')}}" class="form-control" >
            @error('salary')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>

     <label class="col-md-1 col-form-label">{{translate('Slug:')}}
        <span class="text-danger">*</span></label>
    <div class="col-md-2">
        <input type="text" value="{{old('slug')}}" placeholder="{{translate('Slug')}}" name="slug" id="slug" class="form-control" >
        @error('slug')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>
</div>
{{-- end salary --}}

{{-- age --}}
<div class="form-group row">
    <label class="col-md-2 col-form-label">
        {{translate('Age:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-3">
        <input type="text" placeholder="{{translate('Ex: 22 to 30 years')}}"  id="age" name="age" value="{{old('age')}}" class="form-control" >
            @error('age')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>

{{-- end age --}}

{{-- vacancy --}}

    <label class="col-md-1 col-form-label">
        {{translate('vacancy')}}<span class="text-danger">*</span>

    </label>
    <div class="col-md-2">
        <input type="text" placeholder="{{translate('vacancy')}}"  id="vacancy" name="vacancy" value="{{old('vacancy')}}" class="form-control" >
            @error('vacancy')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>

{{-- end vacancy --}}

 {{-- Employment Status --}}

    <label class="col-md-2 col-from-label" style="margin-top: 7px ">
        {{translate('Employment Status:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-2">
        <select class="form-control aiz-selectpicker" name="employment_status" id="employment_status" data-live-search="true" >
            <option value="Full Time"  {{  old('employment_status') ==  'Full Time' ? 'selected' : '' }}>Full Time</option>
            <option value="Part Time"  {{  old('employment_status') ==  'Part Time' ? 'selected' : '' }}>Part Time</option>

        </select>
        @error('employment_status')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>
</div>
{{-- end Employment Status --}}

{{-- Address --}}
<div class="form-group row">
    <label class="col-md-2 col-form-label">
        {{translate('Address:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-4">
        <input type="text" placeholder="{{translate('1234 Main St')}}"  id="address" name="address" value="{{old('address')}}" class="form-control" >
            @error('address')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>

{{-- end Address --}}

{{-- Address 2 --}}

    <label class="col-2 col-form-label">
        {{translate('Address 2:')}}
        <span class="text-danger"></span>
    </label>
    <div class="col-md-4">
        <input type="text" placeholder="{{translate('Apartment, studio, or floor')}}"  id="address2" name="address2" value="{{old('address2')}}" class="form-control" >
            @error('address2')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror

     </div>

{{-- end Address2 --}}




</div>

<div class="form-group row">
    <label class="col-md-2 col-form-label" for="signinSrEmail">
        {{translate('Logo:')}}
        {{-- <small>(1300x650)</small> --}}
    </label>
    <div class="col-md-9">
        <div class="input-group" data-toggle="aizuploader" data-type="image">
            <div class="input-group-prepend">
                <div class="input-group-text bg-soft-secondary font-weight-medium">
                    {{ translate('Browse')}}
                </div>
            </div>
            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
            <input type="hidden" name="banner" class="selected-files">
        </div>
        <div class="file-preview box sm">
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2 col-form-label">
        {{translate('Short Description:')}}
        {{-- <span class="text-danger">*</span> --}}
    </label>
    <div class="col-md-9">
        <textarea name="short_description" rows="5" class="form-control" >{{old('short_description')}}</textarea>
        @error('short_description')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2 col-from-label">
        {{translate('Job Description:')}}
        <span class="text-danger">*</span>
    </label>
    <div class="col-md-9">
        <textarea class="aiz-text-editor" name="job_description" >{{old('job_description')}}</textarea>
        @error('job_description')
 <div class="alert alert-danger">{{ $message }}</div>
            @enderror
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2 col-form-label">{{translate('Meta Title:')}}</label>
    <div class="col-md-9">
        <input value="{{old('meta_title')}}" type="text" class="form-control" name="meta_title" placeholder="{{translate('Meta Title')}}">
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2 col-form-label" for="signinSrEmail">
        {{translate('Meta Image:')}}
        <small>(200x200)+</small>
    </label>
    <div class="col-md-9">
        <div class="input-group" data-toggle="aizuploader" data-type="image">
            <div class="input-group-prepend">
                <div class="input-group-text bg-soft-secondary font-weight-medium">
                    {{ translate('Browse')}}
                </div>
            </div>
            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
            <input value="{{old('meta_img')}}" type="hidden" name="meta_img" class="selected-files">
        </div>
        <div class="file-preview box sm">
        </div>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2 col-form-label">{{translate('Meta Description:')}}</label>
    <div class="col-md-9">
        <textarea value="{{old('meta_description')}}" name="meta_description" rows="5" class="form-control"></textarea>
    </div>
</div>

<div class="form-group row">
    <label class="col-md-2 col-form-label">
        {{translate('Meta Keywords:')}}
    </label>
    <div class="col-md-9">
        <input value="{{old('meta_keywords')}}" type="text" class="form-control" id="meta_keywords" name="meta_keywords" placeholder="{{translate('Meta Keywords')}}">
    </div>
</div>

<div class="form-group mb-0 text-right">
    <button type="submit" class="btn btn-primary">
        {{translate('Save')}}
    </button>
</div>
