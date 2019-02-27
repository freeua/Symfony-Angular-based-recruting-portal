import { Component, ElementRef, HostListener, NgZone, OnInit, ViewChild } from '@angular/core';
import { INgxMyDpOptions } from 'ngx-mydatepicker';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { AdminBusinessAccount } from '../../../../../entities/models-admin';
import { BusinessService } from '../../../../services/business.service';
import { ToastrService } from 'ngx-toastr';
import { SharedService } from '../../../../services/shared.service';
import { articles } from '../../../../constants/articles.const';
import { BusinessJob } from '../../../../../entities/models';
import { IMultiSelectOption, IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import { Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import {} from '@types/googlemaps';
import { MapsAPILoader } from '@agm/core';
import { industry } from '../../../../constants/industry.const';
import { closureDateValidator } from '../../../../validators/custom.validator';

@Component({
  selector: 'app-business-add-new-job',
  templateUrl: './business-add-new-job.component.html',
  styleUrls: ['./business-add-new-job.component.scss']
})
export class BusinessAddNewJobComponent implements OnInit {
  @ViewChild('content') private content : ElementRef;
  @ViewChild('sendEmailPopup') public sendEmailPopup: ElementRef;
  public myOptions: INgxMyDpOptions = { dateFormat: 'yyyy/mm/dd' };
  public businessJobsForm: FormGroup;
  public currentBusinessProfile = new AdminBusinessAccount({});
  public articles = articles;
  public businessJobObject;
  public candidatesMatchingCriteria = 0;
  public articlesFirmPredefined: string[] = [];
  public articlesFirmSelectedName: string[] = [];
  public articlesFirmSettings: IMultiSelectSettings = {
    displayAllSelectedText: true,
    selectionLimit: 0,
    showCheckAll: true,
    showUncheckAll: true,
  };
  public articlesFirmOptions: IMultiSelectOption[] = [];
  public articlesFirmTextConfig: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Articles firm',
    allSelected: 'Articles firm - All selected',
  };
  public switchSteps = true;
  public preloaderPage = true;
  public closureDate: any;
  public modalActiveClose: any;

  public componentForm = {
    street_number: 'short_name',
    route: 'long_name',
    locality: 'long_name',
    administrative_area_level_1: 'short_name',
    country: 'long_name',
    sublocality_level_2: 'long_name',
    postal_code: 'short_name'
  };
  public urlRedirect: string;

  public articlesFirmTextConfigBus: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'Industry',
    allSelected: 'All selected',
  };
  public articlesFirmSettingsBus: IMultiSelectSettings = {
    displayAllSelectedText: true,
    selectionLimit: 0,
    showCheckAll: true,
    showUncheckAll: true,
  };
  public optionsModelBus: string[];
  public indistrySelect: IMultiSelectOption[] = industry;
  public sendEmail = false;

  public specFilesArray = [];
  public checkSpecFiles = false;

  constructor(
    private readonly _businessService: BusinessService,
    private readonly _toastr: ToastrService,
    private readonly _sharedService: SharedService,
    private readonly _router: Router,
    private _modalService: NgbModal,
    private readonly _mapsAPILoader: MapsAPILoader,
    private readonly _ngZone: NgZone
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.articles.forEach((article, index) => {
      this.articlesFirmOptions.push({ id: article, name: article });
    });

    this.businessJobsForm = new FormGroup({
      step1: new FormGroup({
        jobTitle: new FormControl('', Validators.required),
        industry: new FormControl(null, Validators.required),
        companyName: new FormControl('', Validators.required),
        address: new FormControl('', Validators.required),
        addressCountry: new FormControl(''),
        addressState: new FormControl(''),
        addressZipCode: new FormControl(''),
        addressCity: new FormControl(''),
        addressSuburb: new FormControl(''),
        addressStreet: new FormControl(''),
        addressStreetNumber: new FormControl(''),
        addressBuildName: new FormControl(''),
        addressUnit: new FormControl(''),
        companyDescription: new FormControl('', Validators.compose([
          Validators.required,
          Validators.maxLength(300),
        ])),
        roleDescription: new FormControl('', Validators.compose([
          Validators.required,
          Validators.maxLength(400),
        ])),
        closureDate: new FormControl(null, closureDateValidator(new Date())),
        started: new FormControl(null, Validators.required),
        filled: new FormControl(null),
      }),
      step2: new FormGroup({
        articlesFirm: new FormControl([
          'BDO',
          'Deloitte',
          'EY',
          'Grant Thornton',
          'KPMG',
          'Mazaars',
          'Moore Stephens',
          'Ngubane',
          'Nkonki',
          'PWC',
          'SizweNtsalubaGobodo',
          'TOPP',
          'Other'
        ], Validators.required),
        gender: new FormControl('All', Validators.required),
        ethnicity: new FormControl('All', Validators.required),
        nationality: new FormControl(0, Validators.required),
        availability: new FormControl(0, Validators.required),
        location: new FormControl('All', Validators.required),
        qualification: new FormControl(1, Validators.required)
      })
    });

    this.businessJobObject = new BusinessJob({});
    this.fetchCandidatesByCriteria().then(response => {
      this.createJobsForm();
      this.setApplicationClosureDefaultDate();
      this.googleSearch();
    });
  }

  @HostListener('window:beforeunload')
  onBeforeUnload() {
    if (this.businessJobsForm.dirty === true && this.businessJobsForm.touched === true) {
      const confirmTest = "Are you sure you want to leave now?";
      window.event.returnValue = false;
      return confirmTest;
    }
  }

  canDeactivate(url) {
    this.urlRedirect = url;
    if (this.businessJobsForm.dirty === true && this.businessJobsForm.touched === true) {
      this.openVerticallyCentered(this.content);
    }
    else {
      return true;
    }
  }

  /**
   * Upload files
   * @param fieldName {string}
   * @param event {File}
   */
  public uploadFiles(fieldName: string, event) {
    for (let item of event.target.files){
      this.specFilesArray = [];
      this[fieldName].push(item);
    }
  }

  /**
   * Remove file
   * @param fieldName {string}
   * @param index {number}
   * @return {Promise<void>}
   */
  public async removeFile(fieldName: string, index: number): Promise<void> {
    this.specFilesArray = [];
  }

  /**
   * Exit add job page
   */
  exitPage(){
    this.modalActiveClose.dismiss();
    this.businessJobsForm.reset();
    this._router.navigate([this.urlRedirect]);
  }

  public googleSearch() {
    this._mapsAPILoader.load().then(() => {
        const autocomplete = new google.maps.places.Autocomplete((<HTMLInputElement>document.getElementById('search1')), { types:["address"] });

        autocomplete.addListener("place_changed", () => {
          this._ngZone.run(() => {
            const place: google.maps.places.PlaceResult = autocomplete.getPlace();
            this.businessJobsForm.controls['step1'].patchValue({
              address: place.formatted_address,
            });
            this.businessJobsForm.controls['step1'].patchValue({
              addressStreetNumber: '',
              addressSuburb: '',
              addressStreet: '',
              addressCity: '',
              addressState: '',
              addressCountry: '',
              addressZipCode: ''
            });

            for (let i = 0; i < place.address_components.length; i++) {
              let addressType = place.address_components[i].types[0];
              if (addressType === 'sublocality_level_1') {
                addressType = 'sublocality_level_2';
              }
              if (this.componentForm[addressType]) {
                const valuePlace = place.address_components[i][this.componentForm[addressType]];
                (<HTMLInputElement>document.getElementById(addressType)).value = valuePlace;

                if (addressType === 'street_number') {
                  this.businessJobsForm.controls['step1'].patchValue({
                    addressStreetNumber: valuePlace
                  });
                } else if (addressType === 'sublocality_level_2') {
                  this.businessJobsForm.controls['step1'].patchValue({
                    addressSuburb: valuePlace
                  });
                } else if (addressType === 'route') {
                  this.businessJobsForm.controls['step1'].patchValue({
                    addressStreet: valuePlace
                  });
                } else if (addressType === 'locality') {
                  this.businessJobsForm.controls['step1'].patchValue({
                    addressCity: valuePlace
                  });
                } else if (addressType === 'administrative_area_level_1') {
                  this.businessJobsForm.controls['step1'].patchValue({
                    addressState: valuePlace
                  });
                } else if (addressType === 'country') {
                  this.businessJobsForm.controls['step1'].patchValue({
                    addressCountry: valuePlace
                  });
                } else if (addressType === 'postal_code') {
                  this.businessJobsForm.controls['step1'].patchValue({
                    addressZipCode: valuePlace
                  })
                }
              }
            }
            if ( place.geometry === undefined || place.geometry === null ){
              return;
            }
          });
        });
      }
    );
  }

  /**
   * Switch steps
   * @param page
   */
  public switchStep(page): void {
    if (this.businessJobsForm.controls['step1'].invalid) {
      this._sharedService.validateAllFormFieldsJob(this.businessJobsForm.controls['step1']);
      //this._sharedService.validateJobForm(this.businessJobsForm.controls['step1']);
    }
    else {
      this.switchSteps = page;
    }
    this.googleSearch();
  }

  /**
   * creates businessJobsForm and populates with specified data
   * @returns void
   */
  public async createJobsForm(): Promise<void> {
    this.currentBusinessProfile = await this._businessService.getBusinessProfileDetails();

    this.businessJobsForm.controls['step1'].setValue({
      jobTitle: '',
      industry: this.currentBusinessProfile.profile.company.industry,
      companyName: this.currentBusinessProfile.profile.company.name,
      address: this.currentBusinessProfile.profile.company.address,
      addressCountry: this.currentBusinessProfile.profile.company.addressCountry,
      addressState: this.currentBusinessProfile.profile.company.addressState,
      addressZipCode: this.currentBusinessProfile.profile.company.addressZipCode,
      addressCity: this.currentBusinessProfile.profile.company.addressCity,
      addressSuburb: this.currentBusinessProfile.profile.company.addressSuburb,
      addressStreet: this.currentBusinessProfile.profile.company.addressStreet,
      addressStreetNumber: this.currentBusinessProfile.profile.company.addressStreetNumber,
      addressBuildName: this.currentBusinessProfile.profile.company.addressBuildName,
      addressUnit: this.currentBusinessProfile.profile.company.addressUnit,
      companyDescription: this.currentBusinessProfile.profile.company.description,
      roleDescription: null,
      closureDate: this.closureDate,
      started: null,
      filled: null,
    });
    this.businessJobsForm.controls['step2'].setValue({
      articlesFirm: [
        'BDO',
        'Deloitte',
        'EY',
        'Grant Thornton',
        'KPMG',
        'Mazaars',
        'Moore Stephens',
        'Ngubane',
        'Nkonki',
        'PWC',
        'SizweNtsalubaGobodo',
        'TOPP',
        'Other'
      ],
      gender: 'All',
      ethnicity: 'All',
      nationality: 0,
      // video: null,
      availability: 0,
      location: 'All',
      qualification: 1
    });

    this.preloaderPage = false;
  }

  /**
   * sets default date for application closure date equal to 14 days from current date
   * @returns void
   */
  public setApplicationClosureDefaultDate(): void {
    // Set today date using the patchValue function
    const date = new Date(Date.now() + 12096e5);
    this.closureDate = {
        date: {
          year: date.getFullYear(),
          month: date.getMonth() + 1,
          day: date.getDate(),
        }
    }
  }

  /**
   * Send job email
   * @returns {Promise<void>}
   */
  public sendJobEmail(qualification): any{
    if (qualification === 3) {
      this.sendEmail = true;
      try {
        this._businessService.sendJobEmail();
        this.openVerticallyCentered(this.sendEmailPopup);
      }
      catch (err) {
        this._sharedService.showRequestErrors(err);
      }
    }
    else{
      this.sendEmail = false;
    }
  }

  /**
   * creates new job for business
   * @returns void
   */
  public processJobsCreation(): void {
    if (this.businessJobsForm.valid) {

      const formData = new FormData();

      for (let i = 0; i < this.specFilesArray.length; i++) {
        formData.append('spec', this.specFilesArray[i]);
      }

      formData.append('clientID', this.businessJobsForm.controls['step1'].value.companyName);
      formData.append('jobTitle', this.businessJobsForm.controls['step1'].value.jobTitle);
      formData.append('industry', this.businessJobsForm.controls['step1'].value.industry);
      formData.append('companyName', this.businessJobsForm.controls['step1'].value.companyName);
      formData.append('companyAddress', this.businessJobsForm.controls['step1'].value.address);
      formData.append('addressCountry', this.businessJobsForm.controls['step1'].value.addressCountry);
      formData.append('addressState', this.businessJobsForm.controls['step1'].value.addressState);
      formData.append('addressZipCode', this.businessJobsForm.controls['step1'].value.addressZipCode);
      formData.append('addressCity', this.businessJobsForm.controls['step1'].value.addressCity);
      formData.append('addressSuburb', this.businessJobsForm.controls['step1'].value.addressSuburb);
      formData.append('addressStreet', this.businessJobsForm.controls['step1'].value.addressStreet);
      formData.append('addressStreetNumber', this.businessJobsForm.controls['step1'].value.addressStreetNumber);
      formData.append('addressBuildName', this.businessJobsForm.controls['step1'].value.addressBuildName);
      formData.append('addressUnit', this.businessJobsForm.controls['step1'].value.addressUnit);
      formData.append('companyDescription', this.businessJobsForm.controls['step1'].value.companyDescription);
      formData.append('roleDescription', this.businessJobsForm.controls['step1'].value.roleDescription);
      formData.append('closureDate', (this.businessJobsForm.controls['step1'].value.closureDate == null ) ? null : this.businessJobsForm.controls['step1'].value.closureDate.date.day + '.'  + this.businessJobsForm.controls['step1'].value.closureDate.date.month + '.'  + this.businessJobsForm.controls['step1'].value.closureDate.date.year);
      formData.append('started', (this.businessJobsForm.controls['step1'].value.started == null ) ? null : this.businessJobsForm.controls['step1'].value.started.date.day + '.'  + this.businessJobsForm.controls['step1'].value.started.date.month + '.'  + this.businessJobsForm.controls['step1'].value.started.date.year);
      formData.append('filled', (this.businessJobsForm.controls['step1'].value.filled == null ) ? null : this.businessJobsForm.controls['step1'].value.filled.date.day + '.'  + this.businessJobsForm.controls['step1'].value.filled.date.month + '.'  + this.businessJobsForm.controls['step1'].value.filled.date.year);
      formData.append('articlesFirm', this.businessJobsForm.controls['step2'].value.articlesFirm);
      formData.append('gender', (this.businessJobsForm.controls['step2'].value.gender === null) ? null : this.businessJobsForm.controls['step2'].value.gender);
      formData.append('ethnicity', (this.businessJobsForm.controls['step2'].value.ethnicity === null) ? null : this.businessJobsForm.controls['step2'].value.ethnicity);
      formData.append('qualification', this.businessJobsForm.controls['step2'].value.qualification);
      formData.append('nationality', this.businessJobsForm.controls['step2'].value.nationality);
      formData.append('availability', this.businessJobsForm.controls['step2'].value.availability);
      formData.append('location', this.businessJobsForm.controls['step2'].value.location);

      // this.businessJobObject.clientID = this.businessJobsForm.controls['step1'].value.companyName;
      // this.businessJobObject.jobTitle = this.businessJobsForm.controls['step1'].value.jobTitle;
      // this.businessJobObject.industry = this.businessJobsForm.controls['step1'].value.industry;
      // this.businessJobObject.companyName = this.businessJobsForm.controls['step1'].value.companyName;
      // this.businessJobObject.companyAddress = this.businessJobsForm.controls['step1'].value.address;
      // this.businessJobObject.addressCountry = this.businessJobsForm.controls['step1'].value.addressCountry;
      // this.businessJobObject.addressState = this.businessJobsForm.controls['step1'].value.addressState;
      // this.businessJobObject.addressZipCode = this.businessJobsForm.controls['step1'].value.addressZipCode;
      // this.businessJobObject.addressCity = this.businessJobsForm.controls['step1'].value.addressCity;
      // this.businessJobObject.addressSuburb = this.businessJobsForm.controls['step1'].value.addressSuburb;
      // this.businessJobObject.addressStreet = this.businessJobsForm.controls['step1'].value.addressStreet;
      // this.businessJobObject.addressStreetNumber = this.businessJobsForm.controls['step1'].value.addressStreetNumber;
      // this.businessJobObject.addressBuildName = this.businessJobsForm.controls['step1'].value.addressBuildName;
      // this.businessJobObject.addressUnit = this.businessJobsForm.controls['step1'].value.addressUnit;
      // this.businessJobObject.companyDescription = this.businessJobsForm.controls['step1'].value.companyDescription;
      // this.businessJobObject.roleDescription = this.businessJobsForm.controls['step1'].value.roleDescription;
      // this.businessJobObject.closureDate = (this.businessJobsForm.controls['step1'].value.closureDate == null ) ? null : this.businessJobsForm.controls['step1'].value.closureDate.date.day + '.'  + this.businessJobsForm.controls['step1'].value.closureDate.date.month + '.'  + this.businessJobsForm.controls['step1'].value.closureDate.date.year;
      // this.businessJobObject.started = (this.businessJobsForm.controls['step1'].value.started == null ) ? null : this.businessJobsForm.controls['step1'].value.started.date.day + '.'  + this.businessJobsForm.controls['step1'].value.started.date.month + '.'  + this.businessJobsForm.controls['step1'].value.started.date.year;
      // this.businessJobObject.filled = (this.businessJobsForm.controls['step1'].value.filled == null ) ? null : this.businessJobsForm.controls['step1'].value.filled.date.day + '.'  + this.businessJobsForm.controls['step1'].value.filled.date.month + '.'  + this.businessJobsForm.controls['step1'].value.filled.date.year;
      // this.businessJobObject.articlesFirm = this.businessJobsForm.controls['step2'].value.articlesFirm;
      // this.businessJobObject.gender = (this.businessJobsForm.controls['step2'].value.gender === null) ? null : this.businessJobsForm.controls['step2'].value.gender;
      // this.businessJobObject.ethnicity = (this.businessJobsForm.controls['step2'].value.ethnicity === null) ? null : this.businessJobsForm.controls['step2'].value.ethnicity;
      // this.businessJobObject.qualification = this.businessJobsForm.controls['step2'].value.qualification;
      // this.businessJobObject.nationality = this.businessJobsForm.controls['step2'].value.nationality;
      // this.businessJobObject.availability = this.businessJobsForm.controls['step2'].value.availability;
      // this.businessJobObject.location = this.businessJobsForm.controls['step2'].value.location;
      /*if (this.specFilesArray[0]) {
        this.businessJobObject['spec'] = this.specFilesArray[0].name;
      } else {
        this.businessJobObject['spec'] = '';
      }*/
      // this.businessJobObject.video = this.businessJobsForm.controls['step2'].value.video;

      this._businessService.createBusinessJob(formData).then(
        (response) => {
          this._toastr.success('Your job has been submitted to CAs Online for review.');
          this.businessJobsForm.reset();
          this._sharedService.sidebarBusinessBadges.jobAwaiting++;
          this._router.navigate(['/business/awaiting_job']);
        }
      ).catch((err) => {
        if (err.status === 403){
          this.businessJobsForm.reset();
          window.location.reload();
        }
        else if (err.status === 401){
          this.businessJobsForm.reset();
          window.location.reload();
        }
        else {
          this._sharedService.showRequestErrors(err);
        }
      });
    } else {
      this._sharedService.validateAllFormFields(this.businessJobsForm);
    }
  }

  /**
   * sets articles firm criteria
   * @param event
   * @param gender {string}
   * @param qualification {integer}
   * @param nationality {integer}
   * @param ethnicity {string}
   * @param video {integer}
   * @param location {string}
   * @param availability {integer}
   * @returns void
   */
  public async specifiedArticlesFirmCriteria(event, gender?, qualification?, nationality?, ethnicity?, video?, location?, availability?): Promise<void> {
    if (gender === undefined || gender === null) {
      gender = null;
    }
    else if (gender.label){
      gender = gender.label;
    }

    if (ethnicity === undefined || ethnicity === null) {
      ethnicity = null;
    }
    else if (ethnicity.label){
      ethnicity = ethnicity.label;
    }

    if (location === undefined || location === null) {
      location = null;
    }
    else if (location.label){
      location = location.label;
    }

    if (nationality === undefined || nationality === null) {
      nationality = null;
    }
    if (qualification === undefined || qualification === null) {
      qualification = null;
    }
    if (video === undefined || video === null) {
      video = null;
    }
    if (availability === undefined || availability === null) {
      availability = null;
    }
    this.articlesFirmSelectedName = [];
    if (this.articlesFirmPredefined !== null){
      this.articlesFirmPredefined.forEach((index,i) => {
        this.articlesFirmSelectedName.push(this.articlesFirmPredefined[i]);
      });
    }
    this.articlesFirmSelectedName = (this.articlesFirmSelectedName.length === this.articles.length)
      ? new Array('All')
      : this.articlesFirmSelectedName;
    const articlesFirm = (this.articlesFirmSelectedName.length > 0) ? this.articlesFirmSelectedName.join(',') : null;
    try{
      const data = await this._businessService.getBusinessCandidatesCount('', articlesFirm, gender, qualification, nationality, ethnicity, video, location, availability);
      this.candidatesMatchingCriteria = data.countCandidate;
    }
    catch (err) {
      if (err.status === 403){
        this.businessJobsForm.reset();
        window.location.reload();
      }
      else if (err.status === 401){
        this.businessJobsForm.reset();
        window.location.reload();
      }
      else {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * fetches candidate by criterias
   * @param articlesFirm {string}
   * @param gender {string}
   * @param qualification {integer}
   * @param nationality {integer}
   * @param ethnicity {string}
   * @param video {integer]
   * @param location {string}
   * @param availability {integer}
   */
  public async fetchCandidatesByCriteria(articlesFirm?, gender?, qualification?, nationality?, ethnicity?, video?, location?, availability?): Promise<void> {
    if (gender === undefined || gender === null) {
      gender = null;
    }
    else if (gender.label){
      gender = gender.label;
    }

    if (ethnicity === undefined || ethnicity === null) {
      ethnicity = null;
    }
    else if (ethnicity.label){
      ethnicity = ethnicity.label;
    }
    if (nationality === undefined || nationality === null) {
      nationality = null;
    }
    if (location === undefined || location === null) {
      location = null;
    }
    else if (location.label){
      location = location.label;
    }
    if (qualification === undefined || qualification === null) {
      qualification = null;
    }
    if (video === undefined || video === null) {
      video = null;
    }
    if (availability === undefined || availability === null) {
      availability = null;
    }
    try{
      const data = await this._businessService.getBusinessCandidatesCount('', articlesFirm, gender, qualification, nationality, ethnicity, video, location, availability);
      this.candidatesMatchingCriteria = data.countCandidate;
    }
    catch (err){
      if (err.status === 403){
        this.businessJobsForm.reset();
        window.location.reload();
      }
      else if (err.status === 401){
        this.businessJobsForm.reset();
        window.location.reload();
      }
      else {
        this._sharedService.showRequestErrors(err);
      }
    }
  }

  /**
   * Open modal
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, { centered: true });
  }

}
