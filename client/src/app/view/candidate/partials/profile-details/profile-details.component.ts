import { ChangeDetectionStrategy, Component, ElementRef, HostListener, NgZone, OnInit, ViewChild } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { ApiService } from '../../../../services/api.service';
import { AdminCandidateProfile, AdminCandidateProfileNew, AdminCandidateUser, AdminCandidateUserProfileNew } from '../../../../../entities/models-admin';
import { CandidateService } from '../../../../services/candidate.service';
import { MapsAPILoader } from '@agm/core';
import { ToastrService } from 'ngx-toastr';
import {} from '@types/googlemaps';
import { IMultiSelectOption, IMultiSelectSettings, IMultiSelectTexts } from 'angular-2-dropdown-multiselect';
import { INgxMyDpOptions } from 'ngx-mydatepicker';
import { SharedService } from '../../../../services/shared.service';
import {
  ValidateAvailabilityDate,
  ValidateIdNumber, ValidateNumber
} from '../../../../validators/custom.validator';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Router } from '@angular/router';

@Component({
  selector: 'app-profile-details',
  changeDetection: ChangeDetectionStrategy.Default,
  templateUrl: './profile-details.component.html',
  styleUrls: ['./profile-details.component.scss']
})
export class ProfileDetailsComponent implements OnInit {
  @ViewChild('picture') private picture : ElementRef;
  @ViewChild('matricCertificate') private matricCertificate : ElementRef;
  @ViewChild('tertiaryCertificate') private tertiaryCertificate : ElementRef;
  @ViewChild('universityManuscript') private universityManuscript : ElementRef;
  @ViewChild('creditCheck') private creditCheck : ElementRef;
  @ViewChild('cvFiles') private cvFiles : ElementRef;
  @ViewChild('label') public label : ElementRef;
  @ViewChild('saica') public saica : ElementRef;
  @ViewChild('content') private content : ElementRef;

  public candidateProfileDetails: AdminCandidateProfile;
  public candidateProfileDetailsUpdate = new AdminCandidateProfileNew({
    'user': new AdminCandidateUser({}),
    'profile': new AdminCandidateUserProfileNew({})
  });

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

  public myOptionsDate: INgxMyDpOptions = { dateFormat: 'yyyy/mm/dd' };
  public model: any = { date: { year: 2018, month: 10, day: 9 } };

  public preloaderPage = true;
  public preloaderPicture = false;
  public buttonPreloader = false;

  public articlesOther = false;
  public saicaStatus = false;
  public checkSaica = true;

  public checkCvFiles = false;
  public checkMatricCertificate = false;
  public checkTertiaryCertificate = false;
  public checkUniversityManuscript = false;
  public checkCreditFile = false;
  public availabilityPeriodStatus = false;
  public urlRedirect: string;
  public modalActiveClose: any;
  public checksAvailabilityPeriod = false;
  public checksAvailabilityDate = false;

  public visibilityLooking = false;
  public visibilityVisible = false;
  public checkLooking: boolean;
  public checkVisible: boolean;
  public videoUploadPopup = false;
  public visibleActivePopup = false;

  public checkVideo;
  public allowVideo;

  constructor(
    private readonly _apiService: ApiService,
    private readonly _candidateService: CandidateService,
    private readonly _toastr: ToastrService,
    private readonly _mapsAPILoader: MapsAPILoader,
    private readonly _ngZone: NgZone,
    public readonly _sharedService: SharedService,
    private _modalService: NgbModal,
    private readonly _router: Router
  ) {
    this._sharedService.checkSidebar = false;
    this._sharedService.progressBar = Number(localStorage.getItem('progressBar'));
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

    this.candidateForm = new FormGroup({
      firstName: new FormControl('', Validators.required),
      lastName: new FormControl('', Validators.required),
      phone: new FormControl('', [
        Validators.required,
        Validators.minLength(9),
        ValidateNumber
      ]),
      email: new FormControl('', Validators.required),

      saicaStatus: new FormControl(null, [Validators.required]),
      articlesFirm: new FormControl(null, [Validators.required]),
      saicaNumber: new FormControl('', [
        this.saicaValidator('saicaStatus')
      ]),
      articlesFirmName: new FormControl('', [
        this.articlesFirmNameValidator('articlesFirm')
      ]),
      nationality: new FormControl('', Validators.required),
      idNumber: new FormControl('', Validators.compose([
        ValidateIdNumber
      ])),
      ethnicity: new FormControl('', Validators.required),
      gender: new FormControl('', Validators.required),
      dateOfBirth: new FormControl(''),
      dateArticlesCompleted: new FormControl('', Validators.required),
      costToCompany: new FormControl(''),
      criminal: new FormControl(''),
      credit: new FormControl(''),
      boards: new FormControl('', Validators.required),
      otherQualifications: new FormControl(''),
      homeAddress: new FormControl('', Validators.required),
      addressCountry: new FormControl(''),
      addressState: new FormControl(''),
      addressZipCode: new FormControl(''),
      addressCity: new FormControl(''),
      addressSuburb: new FormControl(''),
      addressStreet: new FormControl(''),
      addressStreetNumber: new FormControl(''),
      addressUnit: new FormControl(''),
      availability: new FormControl(''),
      availabilityPeriod: new FormControl(null),

      dateAvailability: new FormControl('', ValidateAvailabilityDate),
      citiesWorking: new FormControl('', Validators.required),
      linkedinUrl: new FormControl(''),
      linkedinCheck: new FormControl(''),
      creditDescription: new FormControl(''),
      criminalDescription: new FormControl(''),
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
                } else if (addressType === 'route') {
                  this.candidateForm.controls.addressStreet.setValue(valuePlace);
                } else if (addressType === 'sublocality_level_2') {
                  this.candidateForm.controls.addressSuburb.setValue(valuePlace);
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

    this.getCandidateProfileDetails();
  }

  @HostListener('window:beforeunload')
  onBeforeUnload() {
    if (this.candidateForm.dirty === true && this.candidateForm.touched === true) {
      const confirmTest = "Are you sure you want to leave now?";
      window.event.returnValue = false;
      return confirmTest;
    }
  }

  canDeactivate(url) {
    this.urlRedirect = url;
    if (this.candidateForm.dirty === true && this.candidateForm.touched === true) {
      this.openVerticallyCentered(this.content);
    }
    else {
      return true;
    }
  }

  /**
   * Select change router
   * @param url
   */
  public routerApplicants(url): void {
    this._router.navigate([url]);
  }

  /**
   * Exit add job page
   */
  exitPage(){
    this.modalActiveClose.dismiss();
    this.candidateForm.markAsPristine();
    this._router.navigate([this.urlRedirect]);
  }

  /**
   * Check select status articles firm
   * @param label
   */
  public checkStatusArticlesFirm(label){
    if(label === 'Other'){
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
   * Get details profile candidate
   * @return {Promise<void>}
   */
  public async getCandidateProfileDetails(): Promise<void>{
    this.candidateProfileDetails = await this._candidateService.getCandidateProfileDetails();

    this.candidateForm.setValue({
      firstName: this.candidateProfileDetails.user.firstName,
      lastName: this.candidateProfileDetails.user.lastName,
      phone: this.candidateProfileDetails.user.phone,
      email: this.candidateProfileDetails.user.email,

      saicaNumber: this.candidateProfileDetails.profile.saicaNumber,
      articlesFirm: this.candidateProfileDetails.profile.articlesFirm,
      saicaStatus: (this.candidateProfileDetails.profile.saicaStatus === 0) ? null : this.candidateProfileDetails.profile.saicaStatus,
      articlesFirmName: this.candidateProfileDetails.profile.articlesFirmName,
      nationality: this.candidateProfileDetails.profile.nationality,
      idNumber: this.candidateProfileDetails.profile.idNumber,
      ethnicity: this.candidateProfileDetails.profile.ethnicity,
      gender: this.candidateProfileDetails.profile.gender,
      dateOfBirth: this.candidateProfileDetails.profile.dateOfBirth,
      dateArticlesCompleted: this.candidateProfileDetails.profile.dateArticlesCompleted,
      costToCompany: this.candidateProfileDetails.profile.costToCompany,
      criminal: (this.candidateProfileDetails.profile.criminal === null) ? false : this.candidateProfileDetails.profile.criminal,
      credit: (this.candidateProfileDetails.profile.credit === null) ? false : this.candidateProfileDetails.profile.credit,
      linkedinCheck: (this.candidateProfileDetails.profile.linkedinCheck === null) ? false : this.candidateProfileDetails.profile.linkedinCheck,
      boards: this.candidateProfileDetails.profile.boards,
      otherQualifications: this.candidateProfileDetails.profile.otherQualifications,
      homeAddress: this.candidateProfileDetails.profile.homeAddress,
      addressCountry: this.candidateProfileDetails.profile.addressCountry,
      addressState: this.candidateProfileDetails.profile.addressState,
      addressZipCode: this.candidateProfileDetails.profile.addressZipCode,
      addressCity: this.candidateProfileDetails.profile.addressCity,
      addressSuburb: this.candidateProfileDetails.profile.addressSuburb,
      addressStreet: this.candidateProfileDetails.profile.addressStreet,
      addressStreetNumber: this.candidateProfileDetails.profile.addressStreetNumber,
      addressUnit: this.candidateProfileDetails.profile.addressUnit,
      availability: (this.candidateProfileDetails.profile.availability === null) ? true : this.candidateProfileDetails.profile.availability,
      availabilityPeriod: this.candidateProfileDetails.profile.availabilityPeriod,
      dateAvailability: this.candidateProfileDetails.profile.dateAvailability,
      citiesWorking: this.candidateProfileDetails.profile.citiesWorking,
      linkedinUrl: this.candidateProfileDetails.profile.linkedinUrl,
      creditDescription: this.candidateProfileDetails.profile.creditDescription,
      criminalDescription: this.candidateProfileDetails.profile.criminalDescription,
      mostRole: this.candidateProfileDetails.profile.mostRole,
      mostEmployer: this.candidateProfileDetails.profile.mostEmployer,
    });

    if(this.candidateProfileDetails.profile.percentage < 50 || !this.candidateProfileDetails.profile.cvFiles ||
      !this.candidateProfileDetails.profile.cvFiles[0] ||
      !this.candidateProfileDetails.profile.cvFiles[0].approved ||
      (this.candidateProfileDetails.allowVideo === false && !this.candidateProfileDetails.profile.video) ||
      (this.candidateProfileDetails.allowVideo === false && this.candidateProfileDetails.profile.video && this.candidateProfileDetails.profile.video.approved === false)) {
      this.checkVisible = false;
      this.checkLooking = false;
      this.visibilityLooking = true;
      this.visibilityVisible = true;
    } else {
      this.checkVisible = this.candidateProfileDetails.profile.visible;
      this.checkLooking = this.candidateProfileDetails.profile.looking;
    }

    this.checkVideo = this.candidateProfileDetails.profile.video;
    this.allowVideo = this.candidateProfileDetails['allowVideo'];

    if(this.candidateProfileDetails.profile.availabilityPeriod === 4){
      this.availabilityPeriodStatus = true;
    }
    if (this.candidateProfileDetails.profile.availability === null){
      this.candidateProfileDetails.profile.availability = true;
    }

    let dateAvailability = new Date(this.candidateProfileDetails.profile.dateAvailability);
    let dateOfBirth = new Date(this.candidateProfileDetails.profile.dateOfBirth);
    let dateArticlesCompleted = new Date(this.candidateProfileDetails.profile.dateArticlesCompleted);

    if (this.candidateProfileDetails.profile.dateAvailability === null){
      dateAvailability = null;
    } else {
      dateAvailability = new Date(this.candidateProfileDetails.profile.dateAvailability);
      this.candidateForm.patchValue({
        dateAvailability: {
          date: {
            year: dateAvailability.getFullYear(),
            month: dateAvailability.getMonth() + 1,
            day: dateAvailability.getDate(),
          }
        }
      });
    }

    if (this.candidateProfileDetails.profile.dateOfBirth === null){
      dateOfBirth = null;
    } else {
      dateOfBirth = new Date(this.candidateProfileDetails.profile.dateOfBirth);
      this.candidateForm.patchValue({
        dateOfBirth: {
          date: {
            year: dateOfBirth.getFullYear(),
            month: dateOfBirth.getMonth() + 1,
            day: dateOfBirth.getDate(),
          }
        }
      });
    }

    if (this.candidateProfileDetails.profile.dateArticlesCompleted === null){
      dateArticlesCompleted = null;
    } else {
      dateArticlesCompleted = new Date(this.candidateProfileDetails.profile.dateArticlesCompleted);

      this.candidateForm.patchValue({
        dateArticlesCompleted: {
          date: {
            year: dateArticlesCompleted.getFullYear(),
            month: dateArticlesCompleted.getMonth() + 1,
            day: dateArticlesCompleted.getDate(),
          }
        }
      });
    }

    this._sharedService.progressBar = Number(this.candidateProfileDetails.profile.percentage);
    this.matricCertificateArray = (this.candidateProfileDetails.profile.matricCertificate === null) ? [] : this.candidateProfileDetails.profile.matricCertificate;
    this.tertiaryCertificateArray = (this.candidateProfileDetails.profile.tertiaryCertificate === null) ? [] : this.candidateProfileDetails.profile.tertiaryCertificate;
    this.universityManuscriptArray = (this.candidateProfileDetails.profile.universityManuscript === null) ? [] : this.candidateProfileDetails.profile.universityManuscript;
    this.creditCheckArray = (this.candidateProfileDetails.profile.creditCheck === null) ? [] : this.candidateProfileDetails.profile.creditCheck;
    this.cvFilesArray = (this.candidateProfileDetails.profile.cvFiles === null) ? [] : this.candidateProfileDetails.profile.cvFiles;
    this.profilePicture = this.candidateProfileDetails.profile.picture;

    this.checkStatusArticlesFirm(this.candidateProfileDetails.profile.articlesFirm);
    this.checkSaicaStatus(this.candidateProfileDetails.profile.saicaStatus);

    this.preloaderPage = false;
  }

  /**
   * Change status candidate
   * @param field {string}
   * @param value {boolean}
   */
  public changeStatusCandidate(field: string, value: boolean){
    let error = true;
    if(this.candidateProfileDetails.profile.percentage < 50) {
      this._toastr.error('Your profile needs to be 50% complete');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if(!this.candidateProfileDetails.profile.cvFiles || this.candidateProfileDetails.profile.cvFiles.length === 0){
      this._toastr.error('You need to upload CV file');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if(this.candidateProfileDetails.profile.cvFiles[0] && !this.candidateProfileDetails.profile.cvFiles[0].approved){
      this._toastr.error('Your CV files is not approved by the administrator');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if (!this.candidateProfileDetails.profile.video && this.candidateProfileDetails.allowVideo === false) {
      this._toastr.error('You need to upload video');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if (this.candidateProfileDetails.profile.video && !this.candidateProfileDetails.profile.video.approved && this.candidateProfileDetails.allowVideo === false) {
      this._toastr.error('Your video is not approved by the administrator');
      error = false;
      if(field === 'looking'){
        this.checkLooking = false;
      }
      else if(field === 'visible'){
        this.checkVisible = false;
      }
    }
    if(field === 'visible'){
      if(this.checkLooking !== true){
        this._toastr.error('You need to turn ON "I am looking for a job"');
        error = false;
        this.visibilityVisible = true;
      }
      else{
        this.visibilityVisible = false;
      }
    }
    if (error === true) {
      if (field === 'looking' && value === false) {
        this.closeLookingPopup(true, false);
      } else if (field === 'visible' && value === true) {
        this.closeVisiblePopup(true, true);
      } else {
        const data = {[field]:value};

        this._candidateService.changeStatusCandidate(data).then(data => {
          this.checkLooking = data.looking;
          this.checkVisible = data.visible;
          if(this.checkLooking === true){
            this.visibilityVisible = false;
          }
          else{
            this.visibilityVisible = true;
          }
          if (field === 'looking') {
            this._toastr.success('Your profile is now active');
          } else if (field === 'visible' && value === true) {
            this._toastr.success('Your profile is now visible to all employers');
          } else {
            this._toastr.success('Your profile is now not visible to all employers');
          }
        }).catch(err => {
          this._sharedService.showRequestErrors(err);
        });
      }
    }
  }


  /**
   * Status looking popup
   * @param value
   * @param check
   */
  public closeLookingPopup(value, check) {
    this.videoUploadPopup = value;
    this.checkLooking = check;
  }

  /**
   * Status visible popup
   * @param value
   * @param check
   */
  public closeVisiblePopup(value, check) {
    this.visibleActivePopup = value;
    if(check === false) {
      this.checkVisible = false;
    } else {
      this.checkVisible = true;
    }
  }

  /**
   * Send request looking job
   * @param field
   * @param value
   */
  public lookingJobToggle(field: string, value: boolean) {
    this.checkLooking = false;
    const data = {[field]:value};

    this._candidateService.changeStatusCandidate(data).then(data => {
      this.checkLooking = data.looking;
      this.checkVisible = data.visible;
      if(this.checkLooking === true){
        this.visibilityVisible = false;
      }
      else{
        this.visibilityVisible = true;
      }
      this._toastr.error('Your profile is now disabled');
    }).catch(err => {
      this._sharedService.showRequestErrors(err);
    });
  }

  /**
   * Send request visible jobs
   * @param field
   * @param value
   */
  public visibleJobToggle(field: string, value: boolean) {
    this.checkVisible = true;
    const data = { [field]: value };

    this._candidateService.changeStatusCandidate(data).then(data => {
      this.checkVisible = data.visible;
      this._toastr.success('Your profile is now visible to all employers');
    }).catch(err => {
      this._sharedService.showRequestErrors(err);
    });
  }

  /**
   * Check criminal value
   */
  public criminalValue(){
    this.candidateProfileDetails.profile.criminal = !this.candidateProfileDetails.profile.criminal;
  }

  /**
   * Check credit value
   */
  public creditValue(){
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
  public availableValue(){
    this.candidateProfileDetails.profile.availability = !this.candidateProfileDetails.profile.availability;
    if (this.candidateProfileDetails.profile.availability === true){
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
      if (this.candidateProfileDetails.profile.availability === false){
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
   * Update profile files
   * @param fieldName {string}
   * @return {Promise<void>}
   */
  public async updateProfileFiles(fieldName: string): Promise<void> {
    const formData = new FormData();
    if (fieldName === 'picture') {
      this.preloaderPicture = true;
    }

    if (fieldName === 'cvFiles') {
      this.checkCvFiles = true;
    }

    if (fieldName === 'matricCertificate') {
      this.checkMatricCertificate = true;
    }

    if (fieldName === 'tertiaryCertificate') {
      this.checkTertiaryCertificate = true;
    }

    if (fieldName === 'universityManuscript') {
      this.checkUniversityManuscript = true;
    }

    if (fieldName === 'creditCheck') {
      this.checkCreditFile = true;
    }

    for (let i = 0; i < this[fieldName].nativeElement.files.length; i++) {
      formData.append(''+ fieldName +'[]', this[fieldName].nativeElement.files[i]);
    }

    try{
      const data = await this._candidateService.updateProfileFiles(formData);

      if(data.files != {} && data.files.matricCertificate) {
        this.checkMatricCertificate = false;
        this.matricCertificateArray = data.files.matricCertificate;
        this.matricCertificate.nativeElement.value = '';
      }

      if (data.files != {} && data.files.tertiaryCertificate) {
        this.checkTertiaryCertificate = false;
        this.tertiaryCertificateArray = data.files.tertiaryCertificate;
        this.tertiaryCertificate.nativeElement.value = '';
      }

      if (data.files != {} && data.files.universityManuscript) {
        this.checkUniversityManuscript = false;
        this.universityManuscriptArray = data.files.universityManuscript;
        this.universityManuscript.nativeElement.value = '';
      }

      if (data.files != {} && data.files.creditCheck) {
        this.checkCreditFile = false;
        this.creditCheckArray = data.files.creditCheck;
        this.creditCheck.nativeElement.value = '';
      }

      if (data.files != {} && data.files.cvFiles) {
        this.checkCvFiles = false;
        this.cvFilesArray = data.files.cvFiles;
        this.candidateProfileDetails.profile.cvFiles = data.files.cvFiles;
        this.cvFiles.nativeElement.value = '';

      }

      if (data.files != {} && data.files.picture) {
        this.profilePicture = data.files.picture;
        this.picture.nativeElement.value = '';
      }

      localStorage.setItem('progressBar', data.percentage);
      this._sharedService.progressBar = Number(localStorage.getItem('progressBar'));

      if (data.percentage < 50
        || !this.cvFilesArray
        || this.cvFilesArray.length === 0) {
        this._sharedService.visibleErrorProfileIcon = true;
      } else {
        this._sharedService.visibleErrorProfileIcon = false;
      }

      setTimeout(() => {
        this.preloaderPicture = false;
      }, 500);

      this._toastr.success('File has been added');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update candidate profile
   * @return {Promise<void>}
   */
  public async updateCandidateProfile(): Promise<void> {
    this.buttonPreloader = true;

    this.candidateProfileDetailsUpdate.user['firstName'] = this.candidateForm.value.firstName;
    this.candidateProfileDetailsUpdate.user['lastName'] = this.candidateForm.value.lastName;
    this.candidateProfileDetailsUpdate.user['phone'] = this.candidateForm.value.phone;
    this.candidateProfileDetailsUpdate.user['email'] = this.candidateForm.value.email;
    this.candidateProfileDetailsUpdate.profile['boards'] = this.candidateForm.value.boards;
    this.candidateProfileDetailsUpdate.profile['articlesFirm'] = this.candidateForm.value.articlesFirm;
    this.candidateProfileDetailsUpdate.profile['saicaStatus'] = this.candidateForm.value.saicaStatus;
    this.candidateProfileDetailsUpdate.profile['articlesFirmName'] = (this.candidateForm.value.articlesFirm !== 'Other') ? '' : this.candidateForm.value.articlesFirmName;
    this.candidateProfileDetailsUpdate.profile['saicaNumber'] = (this.candidateForm.value.saicaStatus !== 1) ? '' : this.candidateForm.value.saicaNumber;
    this.candidateProfileDetailsUpdate.profile['nationality'] = (this.candidateForm.value.nationality === null ) ? null : this.candidateForm.value.nationality;
    this.candidateProfileDetailsUpdate.profile['idNumber'] = this.candidateForm.value.idNumber;
    this.candidateProfileDetailsUpdate.profile['ethnicity'] = (this.candidateForm.value.ethnicity === null ) ? null : this.candidateForm.value.ethnicity;
    this.candidateProfileDetailsUpdate.profile['gender'] = (this.candidateForm.value.gender === null) ? null : this.candidateForm.value.gender;
    this.candidateProfileDetailsUpdate.profile['dateOfBirth'] = (this.candidateForm.value.dateOfBirth === null ) ? null : new Date(this.candidateForm.value.dateOfBirth.date.year + '.' + this.candidateForm.value.dateOfBirth.date.month + '.'  + this.candidateForm.value.dateOfBirth.date.day);
    this.candidateProfileDetailsUpdate.profile['dateArticlesCompleted'] = (this.candidateForm.value.dateArticlesCompleted === null ) ? null : new Date(this.candidateForm.value.dateArticlesCompleted.date.year + '.'  + this.candidateForm.value.dateArticlesCompleted.date.month + '.'  + this.candidateForm.value.dateArticlesCompleted.date.day);
    this.candidateProfileDetailsUpdate.profile['dateAvailability'] = (this.candidateForm.value.dateAvailability === null ) ? null : (this.candidateForm.value.availability === true ) ? null : new Date(this.candidateForm.value.dateAvailability.date.year + '.'  + this.candidateForm.value.dateAvailability.date.month + '.'  + this.candidateForm.value.dateAvailability.date.day);
    this.candidateProfileDetailsUpdate.profile['costToCompany'] = this.candidateForm.value.costToCompany;
    this.candidateProfileDetailsUpdate.profile['criminal'] = this.candidateForm.value.criminal;
    this.candidateProfileDetailsUpdate.profile['credit'] = this.candidateForm.value.credit;
    this.candidateProfileDetailsUpdate.profile['linkedinCheck'] = this.candidateForm.value.linkedinCheck;
    this.candidateProfileDetailsUpdate.profile['otherQualifications'] = this.candidateForm.value.otherQualifications;
    this.candidateProfileDetailsUpdate.profile['homeAddress'] = this.candidateForm.value.homeAddress;
    this.candidateProfileDetailsUpdate.profile['addressCity'] = this.candidateForm.value.addressCity;
    this.candidateProfileDetailsUpdate.profile['addressSuburb'] = this.candidateForm.value.addressSuburb;
    this.candidateProfileDetailsUpdate.profile['addressCountry'] = this.candidateForm.value.addressCountry;
    this.candidateProfileDetailsUpdate.profile['addressState'] = this.candidateForm.value.addressState;
    this.candidateProfileDetailsUpdate.profile['addressStreet'] = this.candidateForm.value.addressStreet;
    this.candidateProfileDetailsUpdate.profile['addressStreetNumber'] =  this.candidateForm.value.addressStreetNumber;
    this.candidateProfileDetailsUpdate.profile['addressUnit'] =  this.candidateForm.value.addressUnit;
    this.candidateProfileDetailsUpdate.profile['addressZipCode'] = this.candidateForm.value.addressZipCode;
    this.candidateProfileDetailsUpdate.profile['availability'] = this.candidateForm.value.availability;
    this.candidateProfileDetailsUpdate.profile['availabilityPeriod'] = this.candidateForm.value.availabilityPeriod;
    this.candidateProfileDetailsUpdate.profile['mostRole'] = this.candidateForm.value.mostRole;
    this.candidateProfileDetailsUpdate.profile['mostEmployer'] = this.candidateForm.value.mostEmployer;
    this.candidateProfileDetailsUpdate.profile['citiesWorking'] = (this.candidateForm.value.citiesWorking === undefined) ? null : this.candidateForm.value.citiesWorking;
    this.candidateProfileDetailsUpdate.profile['linkedinUrl'] = (this.candidateForm.value.linkedinCheck === false) ? null : this.candidateForm.value.linkedinUrl;
    this.candidateProfileDetailsUpdate.profile['creditDescription'] = (this.candidateForm.value.credit === false) ? null : this.candidateForm.value.creditDescription;
    this.candidateProfileDetailsUpdate.profile['criminalDescription'] = (this.candidateForm.value.criminal === false) ? null : this.candidateForm.value.criminalDescription;

    if (this.candidateForm.valid) {
      if(this.cvFilesArray !== null && this.cvFilesArray.length > 0){
        if(this.candidateProfileDetails.profile.availability === false && !this.candidateForm.value.availabilityPeriod){
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
          this._apiService.sendDemoData(this.candidateProfileDetailsUpdate).then(data => {
            localStorage.setItem('progressBar', data.percentage);
            this._sharedService.progressBar = Number(localStorage.getItem('progressBar'));

            this.candidateForm.markAsPristine();
            if (data.percentage < 50
              || !this.cvFilesArray
              || this.cvFilesArray.length === 0) {
              this._sharedService.visibleErrorProfileIcon = true;
            } else {
              this._sharedService.visibleErrorProfileIcon = false;
            }

            this._toastr.success('Profile has been updated');
            this._router.navigate(['/candidate/achievements']);
            this.buttonPreloader = false;
          }).catch(err => {
            this._sharedService.showRequestErrors(err);
            this.buttonPreloader = false;
          })
        }
      }
      else{
        this._toastr.error('Please upload CV');
        this.buttonPreloader = false;
      }
    } else {
      if(this.candidateProfileDetails.profile.availability === false && !this.candidateForm.value.availabilityPeriod){
        this._toastr.error('Availability Period is required');
        this.checksAvailabilityPeriod = true;
      }
      if(this.candidateForm.value.availabilityPeriod === 4 && this.candidateForm.value.dateAvailability === null) {
        this._toastr.error('Date Availability is required');
        this.checksAvailabilityDate = true;
        this.buttonPreloader = false;
      }
      this._sharedService.validateAlertCandidateForm(this.candidateForm);
      this._sharedService.validateAllFormFields(this.candidateForm);
      this.buttonPreloader = false;
    }
  }

  /**
   * Remove file
   * @param fieldName {string}
   * @param url {string}
   * @return {Promise<void>}
   */
  public async removeFile(fieldName: string, url: string): Promise<void> {
    if (fieldName === 'picture') {
      this.preloaderPicture = true;
    }

    try {
      await this._candidateService.removeFile(fieldName, url).then(data => {
        if (fieldName === 'matricCertificate') {
          this.matricCertificateArray = data[fieldName];
        } else if(fieldName === 'tertiaryCertificate') {
          this.tertiaryCertificateArray = data[fieldName];
        } else if (fieldName === 'universityManuscript') {
          this.universityManuscriptArray = data[fieldName];
        } else if (fieldName === 'creditCheck') {
          this.creditCheckArray = data[fieldName];
        } else if (fieldName === 'cvFiles') {
          this.cvFilesArray = data[fieldName];
          this.candidateProfileDetails.profile.cvFiles = data[fieldName];
        } else if (fieldName === 'picture') {
          this.profilePicture = data[fieldName];
          setTimeout(() => {
            this.preloaderPicture = false;
          }, 500);
        }

        localStorage.setItem('progressBar', data.percentage);
        this.checkVisible = data.visible;
        this.checkLooking = data.looking;
        this._sharedService.progressBar = Number(localStorage.getItem('progressBar'));
        if (data.percentage < 50
          || !this.cvFilesArray
          || this.cvFilesArray.length === 0) {
          this._sharedService.visibleErrorProfileIcon = true;
        } else {
          this._sharedService.visibleErrorProfileIcon = false;
        }
      });
      this._toastr.success('File was deleted successfully');
    } catch (err) {
      this._toastr.error(err.error.error);
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
   * validates phone number
   * @param otherControlName
   */
  public validatePhoneNumber(otherControlName: string) {

    return function validatePhoneNumber (control: FormControl) {
      const number = control.value;
      if (number.length >= 8) {
        return null;
      }
      else {
        return { validPhoneNumber: true };
      }
    };
  }

  /**
   * Saica validator
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
   * Open modal
   * @param content
   */
  public openVerticallyCentered(content) {
    this.modalActiveClose = this._modalService.open(content, { centered: true });
  }

}
