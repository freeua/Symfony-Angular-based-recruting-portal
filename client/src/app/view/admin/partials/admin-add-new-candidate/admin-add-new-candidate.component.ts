import { Component, ElementRef, NgZone, OnInit, ViewChild } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { ValidateAvailabilityDate, ValidateIdNumber, ValidateNumber } from '../../../../validators/custom.validator';
import { SharedService } from '../../../../services/shared.service';
import { MapsAPILoader } from '@agm/core';
import { ToastrService } from 'ngx-toastr';
import { CandidateService } from '../../../../services/candidate.service';
import { INgxMyDpOptions } from 'ngx-mydatepicker';
import { IMultiSelectOption, IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import {
  AdminCandidateProfile, AdminCandidateUser,
  AdminCandidateUserProfile
} from '../../../../../entities/models-admin';
import { AdminService } from '../../../../services/admin.service';
import { Router } from '@angular/router';
import {DomSanitizer} from "@angular/platform-browser";

@Component({
  selector: 'app-admin-add-new-candidate',
  templateUrl: './admin-add-new-candidate.component.html',
  styleUrls: ['./admin-add-new-candidate.component.scss']
})
export class AdminAddNewCandidateComponent implements OnInit {

  @ViewChild('picture') private picture : ElementRef;
  @ViewChild('matricCertificate') private matricCertificate : ElementRef;
  @ViewChild('tertiaryCertificate') private tertiaryCertificate : ElementRef;
  @ViewChild('universityManuscript') private universityManuscript : ElementRef;
  @ViewChild('creditCheck') private creditCheck : ElementRef;
  @ViewChild('cvFiles') private cvFiles : ElementRef;
  @ViewChild('video') private video : ElementRef;
  @ViewChild('videoPlayer') private videoPlayer : ElementRef;

  public articlesFirmTextConfig: IMultiSelectTexts = {
    checkAll: 'Select all',
    uncheckAll: 'Deselect all',
    checked: 'item selected',
    checkedPlural: 'items selected',
    defaultTitle: 'I would work in these cities...',
    allSelected: 'All selected',
  };
  public articlesFirmSettings: IMultiSelectSettings = {
    displayAllSelectedText: true,
    selectionLimit: 0,
    showCheckAll: true,
    showUncheckAll: true,
  };
  public articlesFirmOptions: IMultiSelectOption[] = [];

  public candidateProfileDetails: AdminCandidateProfile;
  public candidateForm: FormGroup;
  public componentForm = {
    street_number: 'short_name',
    route: 'long_name',
    locality: 'long_name',
    administrative_area_level_1: 'short_name',
    country: 'long_name',
    sublocality_level_2: 'long_name',
    postal_code: 'short_name'
  };

  public articles = [
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
  ];

  public optionsModel: string[];
  public myOptions: IMultiSelectOption[];

  public matricCertificateArray = [];
  public tertiaryCertificateArray = [];
  public universityManuscriptArray = [];
  public creditCheckArray = [];
  public cvFilesArray = [];
  public profilePicture = [];
  public profileVideo = null;

  public myOptionsDate: INgxMyDpOptions = { dateFormat: 'yyyy/mm/dd' };
  public model: any = { date: { year: 2018, month: 10, day: 9 } };

  public preloaderPage = true;
  public buttonPreloader = false;
  public preloaderPicture = false;

  public articlesOther = false;
  public saicaStatus = false;
  public checkSaica = true;

  public availabilityPeriodStatus = false;
  public checksAvailabilityPeriod = false;
  public checksAvailabilityDate = false;

  constructor(
    private readonly _candidateService: CandidateService,
    private readonly _toastr: ToastrService,
    private readonly _mapsAPILoader: MapsAPILoader,
    private readonly _ngZone: NgZone,
    private readonly _sharedService: SharedService,
    private readonly _adminService: AdminService,
    private readonly _router: Router,
    private readonly _sanitizer: DomSanitizer
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.myOptions = [
      { id: 'Johannesburg', name: 'Johannesburg' },
      { id: 'Cape Town', name: 'Cape Town' },
      { id: 'Pretoria', name: 'Pretoria' },
      { id: 'Durban', name: 'Durban' },
      { id: 'International', name: 'International' },
      { id: 'Other', name: 'Other' }
    ];
    this.candidateProfileDetails = new AdminCandidateProfile({
      profile: new AdminCandidateUserProfile({}),
      user: new AdminCandidateUser({})
    });

    this.candidateForm = new FormGroup({
      firstName: new FormControl(null, Validators.required),
      lastName: new FormControl(null, Validators.required),
      phone: new FormControl('', [
        Validators.required,
        ValidateNumber
      ]),
      email: new FormControl(null, Validators.required),

      articlesFirm: new FormControl(null, Validators.required),
      saicaStatus: new FormControl(null, Validators.required),
      saicaNumber: new FormControl(null, [
        this.saicaValidator('saicaStatus')
      ]),
      articlesFirmName: new FormControl(null, [
        this.articlesFirmNameValidator('articlesFirm')
      ]),
      nationality: new FormControl(null, Validators.required),
      idNumber: new FormControl(null, Validators.compose([
        ValidateIdNumber
      ])),
      ethnicity: new FormControl(null, Validators.required),
      gender: new FormControl(null, Validators.required),
      dateOfBirth: new FormControl(null),
      dateArticlesCompleted: new FormControl(null, Validators.required),
      costToCompany: new FormControl(null),
      criminal: new FormControl(false),
      credit: new FormControl(false),
      boards: new FormControl(null, Validators.required),
      otherQualifications: new FormControl(null),
      homeAddress: new FormControl(null, Validators.required),
      addressCountry: new FormControl(null),
      addressState: new FormControl(null),
      addressZipCode: new FormControl(null),
      addressCity: new FormControl(null),
      addressSuburb: new FormControl(null),
      addressStreet: new FormControl(null),
      addressStreetNumber: new FormControl(null),
      addressUnit: new FormControl(null),
      availability: new FormControl(true),
      availabilityPeriod: new FormControl(null),
      dateAvailability: new FormControl(null, ValidateAvailabilityDate),
      citiesWorking: new FormControl(null, Validators.required),
      linkedinUrl: new FormControl(null),
      linkedinCheck: new FormControl(false),
      creditDescription: new FormControl(null),
      criminalDescription: new FormControl(null),
      mostRole: new FormControl('', Validators.required),
      mostEmployer: new FormControl('', Validators.required)
    });

    this._mapsAPILoader.load().then(() => {
        const autocomplete = new google.maps.places.Autocomplete((<HTMLInputElement>document.getElementById('search1')), { types:["address"] });

        autocomplete.addListener("place_changed", () => {
          this._ngZone.run(() => {
            const place: google.maps.places.PlaceResult = autocomplete.getPlace();

            this.candidateForm.controls.homeAddress.setValue(place.formatted_address);
            this.candidateForm.controls['addressStreetNumber'].setValue('');
            this.candidateForm.controls['addressStreet'].setValue('');
            this.candidateForm.controls['addressSuburb'].setValue('');
            this.candidateForm.controls['addressCity'].setValue('');
            this.candidateForm.controls['addressState'].setValue('');
            this.candidateForm.controls['addressCountry'].setValue('');
            this.candidateForm.controls['addressZipCode'].setValue('');
            for (let i = 0; i < place.address_components.length; i++) {
              let addressType = place.address_components[i].types[0];
              if (addressType === 'sublocality_level_1') {
                addressType = 'sublocality_level_2';
              }
              if (this.componentForm[addressType]) {
                const valuePlace = place.address_components[i][this.componentForm[addressType]];
                (<HTMLInputElement>document.getElementById(addressType)).value = valuePlace;

                if (addressType === 'street_number') {
                  this.candidateForm.controls.addressStreetNumber.setValue(valuePlace);
                } else if (addressType === 'sublocality_level_2') {
                  this.candidateForm.controls.addressSuburb.setValue(valuePlace);
                } else if (addressType === 'route') {
                  this.candidateForm.controls.addressStreet.setValue(valuePlace);
                } else if (addressType === 'locality') {
                  this.candidateForm.controls.addressCity.setValue(valuePlace);
                } else if (addressType === 'administrative_area_level_1') {
                  this.candidateForm.controls.addressState.setValue(valuePlace);
                } else if (addressType === 'country') {
                  this.candidateForm.controls.addressCountry.setValue(valuePlace);
                } else if (addressType === 'postal_code') {
                  this.candidateForm.controls.addressZipCode.setValue(valuePlace);
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

    setTimeout(() => {
      this.preloaderPage = false;
    }, 500);
  }

  /**
   * Upload profile picture
   * @param event {File}
   */
  public uploadPicture(event){
    this.profilePicture = [];
    if (event.target.files && event.target.files[0]) {
      let reader = new FileReader();

      reader.onload = (event: ProgressEvent) => {
        this.profilePicture.push({
          url : (<FileReader>event.target).result
        });
      };

      reader.readAsDataURL(event.target.files[0]);
    }
  }

  /**
   * Upload profile video
   * @param event {File}
   */
  public uploadVideo(event){
    this.profileVideo = [];
    if (event.target.files && event.target.files[0]) {
      let reader = new FileReader();

      reader.onload = (event: ProgressEvent) => {
        this.profileVideo.push({
          url : (<FileReader>event.target).result
        });
        this.videoPlayer.nativeElement.src = (<FileReader>event.target).result;
        this.videoPlayer.nativeElement.load();
      };

      reader.readAsDataURL(event.target.files[0]);
    }
  }

  /**
   * Upload files
   * @param fieldName {string}
   * @param event {File}
   */
  public uploadFiles(fieldName: string, event) {
    for (let item of event.target.files){
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
    this[fieldName].splice(index, 1);
  }

  public removePicture() {
    this.profilePicture = [];
    this.picture.nativeElement.value = '';
  }

  public removeVideo() {
    this.profileVideo = null;
    this.videoPlayer.nativeElement.src = '';
    this.video.nativeElement.value = '';
  }

  /**
   * Check select status articles firm
   * @param label
   */
  public checkStatusArticlesFirm(label){
    if (label === 'Other'){
      this.articlesOther = true;
    }
    else{
      this.articlesOther = false;
    }
  }

  /**
   * Check SAICA status
   * @param label
   */
  public checkSaicaStatus(label){
    if(label === 1){
      this.saicaStatus = true;
      this.checkSaica = true;
    }
    else{
      this.saicaStatus = false;
      this.checkSaica = true;
    }
  }

  /**
   * Articles Firm Name validator
   * @param otherControlName {string}
   * @return {(control:FormControl)=>(null|{matchOther: boolean})}
   */
  public articlesFirmNameValidator (otherControlName: string) {
    let thisControl: FormControl;
    let otherControl: FormControl;
    return function articlesFirmNameValidator (control: FormControl) {
      if (!control.parent) {
        return null;
      }
      // Initializing the validator.
      if (!thisControl) {
        thisControl = control;
        otherControl = control.parent.get(otherControlName) as FormControl;
        if (!otherControl) {
          throw new Error('matchOtherValidator(): other control is not found in parent group');
        }
        otherControl.valueChanges.subscribe(() => {
          thisControl.updateValueAndValidity();
        });
      }
      if (!otherControl) {
        return null;
      }
      if (otherControl.value === 'Other' && !thisControl.value) {
        return {
          matchOther: true
        };
      }
      return null;
    };
  }

  /**
   * Password validator
   * @param otherControlName {string}
   * @return {(control:FormControl)=>(null|{matchOther: boolean})}
   */
  public saicaValidator (otherControlName: string) {
    let thisControl: FormControl;
    let otherControl: FormControl;
    return function saicaValidator (control: FormControl) {
      if (!control.parent) {
        return null;
      }
      // Initializing the validator.
      if (!thisControl) {
        thisControl = control;
        otherControl = control.parent.get(otherControlName) as FormControl;
        if (!otherControl) {
          throw new Error('matchOtherValidator(): other control is not found in parent group');
        }
        otherControl.valueChanges.subscribe(() => {
          thisControl.updateValueAndValidity();
        });
      }
      if (!otherControl) {
        return null;
      }
      if (otherControl.value === 1 && !thisControl.value) {
        return {
          matchOther: true
        };
      }
      return null;
    };
  }

  /**
   * Check criminal value
   */
  public criminalValue(): void {
    this.candidateProfileDetails.profile.criminal = !this.candidateProfileDetails.profile.criminal;
  }

  /**
   * Check credit value
   */
  public creditValue(): void {
    this.candidateProfileDetails.profile.credit = !this.candidateProfileDetails.profile.credit;
  }

  /**
   * Check linkedinCheck value
   */
  public linkedinValue(){
      this.candidateProfileDetails.profile.linkedinCheck = !this.candidateProfileDetails.profile.linkedinCheck;
  }

  /**
   * Check availability value
   */
  public availableValue(value){
    value = !value;

    if (value === true){
      this.candidateForm.get('availabilityPeriod').setValue(null, { onlySelf: true });
      this.availabilityPeriodStatus = false;
      this.checksAvailabilityPeriod = false;
      this.checksAvailabilityDate = false;
    }
  }

  /**
   * Check availability period
   * @param select {number}
   */
  public checkAvailabilityPeriod(select) {
    this.checksAvailabilityPeriod = false;
    this.checksAvailabilityDate = false;
    if(select === 4){
      if (this.candidateForm.value.availability === false){
        this.availabilityPeriodStatus = true;
      }
      else{
        this.candidateForm.get('dateAvailability').setValue(null, { onlySelf: true });
        this.availabilityPeriodStatus = false;
      }
    }
    else{
      this.candidateForm.get('dateAvailability').setValue(null, { onlySelf: true });
      this.availabilityPeriodStatus = false;
    }
  }

  /**
   * Create new candidate
   * @return {Promise<void>}
   */
  public async createCandidateProfile(): Promise<void> {
    this.buttonPreloader = true;

    const formData = new FormData();

    for (let i = 0; i < this.matricCertificateArray.length; i++) {
      formData.append('matricCertificate[]', this.matricCertificateArray[i]);
    }
    for (let i = 0; i < this.tertiaryCertificateArray.length; i++) {
      formData.append('tertiaryCertificate[]', this.tertiaryCertificateArray[i]);
    }
    for (let i = 0; i < this.universityManuscriptArray.length; i++) {
      formData.append('universityManuscript[]', this.universityManuscriptArray[i]);
    }
    for (let i = 0; i < this.creditCheckArray.length; i++) {
      formData.append('creditCheck[]', this.creditCheckArray[i]);
    }
    for (let i = 0; i < this.cvFilesArray.length; i++) {
      formData.append('cvFiles[]', this.cvFilesArray[i]);
    }

    formData.append('user[firstName]',this.candidateForm.value.firstName);
    formData.append('user[lastName]',this.candidateForm.value.lastName);
    formData.append('user[phone]', this.candidateForm.value.phone);
    formData.append('user[email]',this.candidateForm.value.email);

    formData.append('profile[articlesFirm]', this.candidateForm.value.articlesFirm);
    formData.append('profile[saicaStatus]', this.candidateForm.value.saicaStatus);
    formData.append('profile[articlesFirmName]', (this.candidateForm.value.articlesFirm !== 'Other') ? '' : this.candidateForm.value.articlesFirmName);
    formData.append('profile[saicaNumber]', (this.candidateForm.value.saicaStatus !== 1) ? '' : this.candidateForm.value.saicaNumber);
    formData.append('profile[nationality]', (this.candidateForm.value.nationality === null ) ? null : this.candidateForm.value.nationality);
    formData.append('profile[idNumber]', this.candidateForm.value.idNumber);
    formData.append('profile[boards]', this.candidateForm.value.boards);
    formData.append('profile[ethnicity]', this.candidateForm.value.ethnicity);
    formData.append('profile[gender]', this.candidateForm.value.gender);
    formData.append('profile[dateOfBirth]', (this.candidateForm.value.dateOfBirth === null ) ? null : this.candidateForm.value.dateOfBirth.formatted);
    formData.append('profile[dateArticlesCompleted]', (this.candidateForm.value.dateArticlesCompleted === null ) ? null : this.candidateForm.value.dateArticlesCompleted.formatted);
    formData.append('profile[dateAvailability]', (this.candidateForm.value.dateAvailability === null ) ? null : this.candidateForm.value.dateAvailability.formatted);
    formData.append('profile[costToCompany]', this.candidateForm.value.costToCompany);
    formData.append('profile[criminal]', this.candidateForm.value.criminal);
    formData.append('profile[credit]', this.candidateForm.value.credit);
    formData.append('profile[linkedinCheck]', this.candidateForm.value.linkedinCheck);
    formData.append('profile[otherQualifications]', this.candidateForm.value.otherQualifications);
    formData.append('profile[homeAddress]', this.candidateForm.value.homeAddress);
    formData.append('profile[addressCity]', this.candidateForm.value.addressCity);
    formData.append('profile[addressSuburb]', this.candidateForm.value.addressSuburb);
    formData.append('profile[addressCountry]', this.candidateForm.value.addressCountry);
    formData.append('profile[addressState]', this.candidateForm.value.addressState);
    formData.append('profile[addressStreet]', this.candidateForm.value.addressStreet);
    formData.append('profile[addressStreetNumber]', this.candidateForm.value.addressStreetNumber);
    formData.append('profile[addressUnit]', this.candidateForm.value.addressUnit);
    formData.append('profile[addressZipCode]', this.candidateForm.value.addressZipCode);
    formData.append('profile[availability]', this.candidateForm.value.availability);
    formData.append('profile[availabilityPeriod]', this.candidateForm.value.availabilityPeriod);
    formData.append('profile[mostRole]', this.candidateForm.value.mostRole);
    formData.append('profile[mostEmployer]', this.candidateForm.value.mostEmployer);
    formData.append('profile[citiesWorking]', (this.candidateForm.value.citiesWorking === undefined) ? null : this.candidateForm.value.citiesWorking);
    formData.append('profile[linkedinUrl]', (this.candidateForm.value.linkedinCheck === false) ? null : this.candidateForm.value.linkedinUrl);
    formData.append('profile[creditDescription]', (this.candidateForm.value.credit === false) ? null :this.candidateForm.value.creditDescription);
    formData.append('profile[criminalDescription]', (this.candidateForm.value.criminal === false) ? null : this.candidateForm.value.criminalDescription);

    if(this.picture.nativeElement.files.length > 0){
      formData.append('picture', this.picture.nativeElement.files[0]);
    }
    if(this.video.nativeElement.files.length > 0){
      formData.append('video', this.video.nativeElement.files[0]);
    }

    if (this.candidateForm.valid) {
      if(formData.get('cvFiles[]') !== null){
        if(this.candidateForm.value.availability === false && !this.candidateForm.value.availabilityPeriod){
          this._toastr.error('Availability Period is required');
          this.checksAvailabilityPeriod = true;
          this.buttonPreloader = false;
        }
        else if(this.candidateForm.value.availabilityPeriod === 4 && this.candidateForm.value.dateAvailability === null) {
          this._toastr.error('Date Availability is required');
          this.checksAvailabilityDate = true;
          this.buttonPreloader = false;
        }
        else{
          try {
            await this._adminService.createdCandidateProfile(formData);
            this._toastr.success('Candidate has been created');
            this._sharedService.sidebarAdminBadges.candidateAll++;
            this.buttonPreloader = false;
            this._router.navigate(['/admin/all_candidates']);
          }
          catch(err) {
            this._sharedService.showRequestErrors(err);
            this.buttonPreloader = false;
          }
        }
      }
      else{
        this._toastr.error('Please upload CV');
        this.buttonPreloader = false;
      }
    } else {
      this.buttonPreloader = false;
      if(this.candidateProfileDetails.profile.availability === false && !this.candidateForm.value.availabilityPeriod){
        this._toastr.error('Availability Period is required');
        this.checksAvailabilityPeriod = true;
      }
      this._sharedService.validateAlertCandidateForm(this.candidateForm);
      this._sharedService.validateAllFormFields(this.candidateForm);
    }
    this.buttonPreloader = false;
  }

}
