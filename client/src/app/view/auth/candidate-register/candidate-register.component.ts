import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { CandidateUser, Role } from '../../../../entities/models';
import { ApiService } from '../../../services/api.service';
import { Router } from '@angular/router';
import { SharedService } from '../../../services/shared.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ValidateNumber } from '../../../validators/custom.validator';
import { INgxMyDpOptions } from 'ngx-mydatepicker';

@Component({
  selector: 'app-candidate-register',
  templateUrl: './candidate-register.component.html',
  styleUrls: ['./candidate-register.component.scss']
})
export class CandidateRegisterComponent implements OnInit {

  @ViewChild('register') public register : ElementRef;

  public candidateRegisterForm: FormGroup;
  public check = true;
  public emailCheck = false;
  public errorEmail: string;
  public errorRegistration = [];
  public buttonPreloader = false;
  public modalActiveClose: any;

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

  public afterRegistration = true;
  public afterReg = false;
  public terms = false;
  public articlesOther = false;

  public saicaStatus = false;
  public checkSaica = true;

  public myOptionsDate: INgxMyDpOptions = { dateFormat: 'yyyy/mm/dd' };
  public model: any = { date: { year: 2018, month: 10, day: 9 } };

  constructor(
    private readonly _apiService: ApiService,
    private _router: Router,
    private _sharedService: SharedService,
    private readonly _modalService: NgbModal
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.candidateRegisterForm = new FormGroup({
      firstName: new FormControl('', [
        Validators.required,
        Validators.minLength(2)
      ]),
      lastName: new FormControl('', [
        Validators.required,
        Validators.minLength(2)
      ]),
      phone: new FormControl('', [
        Validators.required,
        ValidateNumber
      ]),
      email: new FormControl('', [
        Validators.required,
        Validators.email
      ]),
      /*boards: new FormControl(null, [
          Validators.required
      ]),*/
      articlesFirm: new FormControl(null, [
        Validators.required
      ]),
      dateArticlesCompleted: new FormControl('', [
        Validators.required
      ]),
      saicaStatus: new FormControl(null, [Validators.required]),
      password: new FormControl('', [
        Validators.required,
        Validators.minLength(6)
      ]),
      verifyPassword: new FormControl('', [
        Validators.required,
        this.matchOtherValidator('password'),
        Validators.minLength(6)
      ]),
      articlesFirmName: new FormControl('', [
        this.articlesFirmNameValidator('articlesFirm')
      ]),
      saicaNumber: new FormControl('', [
        this.saicaValidator('saicaStatus')
      ])
    });
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
    else if (label === 4){
      this.checkSaica = false;
      this.saicaStatus = false;
      this.openVerticallyCentered(this.register);
    }
    else{
      this.saicaStatus = false;
      this.checkSaica = true;
    }
  }

  /**
   * open terms popup
   */
  public openPopup(){
    this.terms = true;
    this.afterReg = false;
    this.afterRegistration = false;
  }

  /**
   * close terms popup
   */
  public closePopup(){
    this.terms = false;
    this.afterReg = false;
    this.afterRegistration = true;
  }

  /**
   * Check terms
   */
  public checkTerms(){
    this.check = false;
    this.terms = false;
    this.afterReg = false;
    this.afterRegistration = true;
  }

  /**
   * Check Terms
   */
  public cutCheckTerms() {
    if (this.check === false) {
      this.check = true;
    } else {
      this.check = false;
    }
  }

  /**
   * Create candidate
   */
  public createCandidateUser () {
    this.buttonPreloader = true;
    const user = new CandidateUser({
      role: Role.candidateRole,
      firstName: this.candidateRegisterForm.value.firstName,
      lastName: this.candidateRegisterForm.value.lastName,
      phone: this.candidateRegisterForm.value.phone,
      email: this.candidateRegisterForm.value.email,
      saicaNumber: this.candidateRegisterForm.value.saicaNumber,
      //boards: this.candidateRegisterForm.value.boards,
      dateArticlesCompleted: this.candidateRegisterForm.value.dateArticlesCompleted.date.day + '.'  + this.candidateRegisterForm.value.dateArticlesCompleted.date.month + '.'  + this.candidateRegisterForm.value.dateArticlesCompleted.date.year,
      articlesFirm: this.candidateRegisterForm.value.articlesFirm,
      articlesFirmName: this.candidateRegisterForm.value.articlesFirmName,
      saicaStatus: this.candidateRegisterForm.value.saicaStatus,
      password: this.candidateRegisterForm.value.password,
      verifyPassword: this.candidateRegisterForm.value.verifyPassword
    });

    if (
      this.candidateRegisterForm.valid
      && this.checkSaica
      && this.check === false
    ) {
      this._apiService.createUser(user).then(() => {
        this.emailCheck = false;
        this.buttonPreloader = false;
        this.terms = false;
        this.afterReg = true;
        this.afterRegistration = false;
      }).catch((err) => {

        this.buttonPreloader = false;
        this.afterRegistration = true;
        this.errorRegistration = [];
        this.errorEmail = '';
        if(err.error.message){
          this.errorRegistration.push(err.error.message);
        }
        else if(err.error.error){
          err.error.error.forEach((errorText) => {
            if(errorText === 'email already use'){
              this.emailCheck = true;
              this.errorEmail = errorText;
            }
            else{
              this.errorRegistration.push(errorText);
            }
          });
        }
      });
    }
    else {
      this.buttonPreloader = false;
    }
  }

  /**
   * Back to login page
   */
  public backToLogin(): void{
    this._router.navigate(['/login']);
  }

  /**
   * Password validator
   * @param otherControlName {string}
   * @return {(control:FormControl)=>(null|{matchOther: boolean})}
   */
  public matchOtherValidator (otherControlName: string) {
    let thisControl: FormControl;
    let otherControl: FormControl;
    return function matchOtherValidate (control: FormControl) {
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
      if (otherControl.value !== thisControl.value) {
        return {
          matchOther: true
        };
      }
      return null;
    };
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
   * Managed modal
   * @param content {any} - content to be shown in popup
   */
  public openVerticallyCentered(content: any) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'sm' });
  }
}
